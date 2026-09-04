<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeamMemberRequest;
use App\Http\Requests\UpdateTeamMemberRequest;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TeamMemberController extends Controller
{
    public function store(StoreTeamMemberRequest $request, Team $team): RedirectResponse
    {
        abort_unless($team->isOwnedBy($request->user()), 404);

        $member = User::query()->where('email', $request->string('email'))->firstOrFail();

        if ($member->is($request->user())) {
            return back()->withErrors(['email' => 'Pemilik sudah menjadi bagian dari tim.']);
        }

        $team->members()->syncWithoutDetaching([$member->id => ['role' => 'member']]);

        return back()->with('status', 'Anggota berhasil ditambahkan.');
    }

    public function destroy(Request $request, Team $team, User $user): RedirectResponse
    {
        abort_unless($team->canManageMembers($request->user()), 404);

        $memberRole = $team->members()->whereKey($user->id)->first()?->pivot?->role;
        abort_unless($memberRole !== null, 404);
        abort_if(! $team->isOwnedBy($request->user()) && $memberRole === 'admin', 404);

        $team->members()->detach($user);

        return back()->with('status', 'Anggota dikeluarkan dari tim.');
    }

    public function update(UpdateTeamMemberRequest $request, Team $team, User $user): RedirectResponse
    {
        abort_unless($team->isOwnedBy($request->user()), 404);
        abort_unless($team->members()->whereKey($user->id)->exists(), 404);

        $team->members()->updateExistingPivot($user->id, ['role' => $request->string('role')->toString()]);

        return back()->with('status', 'Role anggota berhasil diperbarui.');
    }
}
