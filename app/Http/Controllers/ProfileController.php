<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\Board;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user()
            ->load('whatsappConnection')
            ->loadCount(['teams', 'joinedTeams', 'tasks']);
        $projectsCount = Board::query()
            ->whereHas('team', function ($query) use ($user): void {
                $query->where('owner_id', $user->id)
                    ->orWhereHas('members', fn ($memberQuery) => $memberQuery->whereKey($user->id));
            })
            ->count();

        return view('profile.show', compact('user', 'projectsCount'));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $emailChanged = $user->email !== $request->string('email')->toString();

        $user->fill($request->validated());
        if ($emailChanged) {
            $user->email_verified_at = null;
        }
        $user->save();

        return back()->with('status', 'Profil berhasil diperbarui.');
    }
}
