<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeamInvitationRequest;
use App\Mail\TeamInvitationMail;
use App\Models\EmailNotificationLog;
use App\Models\Team;
use App\Models\TeamInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class TeamInvitationController extends Controller
{
    public function store(StoreTeamInvitationRequest $request, Team $team): RedirectResponse
    {
        $team->load(['owner', 'members']);
        abort_unless($team->canManageMembers($request->user()), 404);

        $email = Str::lower($request->string('email')->toString());
        $role = $request->string('role')->toString();

        abort_if(! $team->isOwnedBy($request->user()) && $role === 'admin', 404);

        if ($team->owner->email === $email || $team->members->contains(fn ($member) => Str::lower($member->email) === $email)) {
            throw ValidationException::withMessages(['email' => 'Pengguna tersebut sudah menjadi bagian dari tim.']);
        }

        $token = Str::random(64);
        $invitation = $team->invitations()->updateOrCreate(
            ['email' => $email, 'accepted_at' => null],
            [
                'inviter_id' => $request->user()->id,
                'role' => $role,
                'token_hash' => hash('sha256', $token),
                'expires_at' => now()->addDays(7),
            ],
        );

        $invitation->load(['team', 'inviter']);
        $subject = 'Undangan bergabung ke tim '.$team->name;

        try {
            Mail::to($email)->send(new TeamInvitationMail($invitation, $token));
            EmailNotificationLog::query()->create([
                'team_id' => $team->id,
                'sender_id' => $request->user()->id,
                'recipient' => $email,
                'event' => 'team_invitation',
                'subject' => $subject,
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (Throwable $exception) {
            EmailNotificationLog::query()->create([
                'team_id' => $team->id,
                'sender_id' => $request->user()->id,
                'recipient' => $email,
                'event' => 'team_invitation',
                'subject' => $subject,
                'status' => 'failed',
                'error_at' => now(),
                'error_message' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        return back()->with('status', 'Undangan berhasil dikirim ke '.$email.'.');
    }

    public function show(Request $request, TeamInvitation $teamInvitation, string $token): View
    {
        $this->ensureInvitationCanBeAccepted($request, $teamInvitation, $token);
        $teamInvitation->load(['team', 'inviter']);

        return view('team-invitations.show', compact('teamInvitation', 'token'));
    }

    public function accept(Request $request, TeamInvitation $teamInvitation, string $token): RedirectResponse
    {
        $this->ensureInvitationCanBeAccepted($request, $teamInvitation, $token);

        $teamInvitation->team->members()->syncWithoutDetaching([
            $request->user()->id => ['role' => $teamInvitation->role],
        ]);
        $teamInvitation->update(['accepted_at' => now()]);

        return redirect()->route('teams.show', $teamInvitation->team)->with('status', 'Selamat datang di tim!');
    }

    public function destroy(Request $request, TeamInvitation $teamInvitation): RedirectResponse
    {
        $teamInvitation->load('team.members');
        abort_unless($teamInvitation->team->canManageMembers($request->user()), 404);

        $teamInvitation->delete();

        return back()->with('status', 'Undangan dibatalkan.');
    }

    private function ensureInvitationCanBeAccepted(Request $request, TeamInvitation $invitation, string $token): void
    {
        abort_unless(Str::lower($request->user()->email) === Str::lower($invitation->email), 404);
        abort_unless($invitation->isValidToken($token), 404);
    }
}
