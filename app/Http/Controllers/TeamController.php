<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function show(Request $request, Team $team): View
    {
        abort_unless($team->isAccessibleBy($request->user()), 404);

        $team->load([
            'owner:id,name,email',
            'members:id,name,email',
            'boards' => fn ($query) => $query->orderBy('name'),
            'invitations' => fn ($query) => $query->whereNull('accepted_at')->latest(),
        ]);

        return view('teams.show', compact('team'));
    }

    public function store(StoreTeamRequest $request): RedirectResponse
    {
        $request->user()->teams()->create([
            ...$request->validated(),
            'slug' => Str::slug($request->string('name')).'-'.Str::lower(Str::random(5)),
        ]);

        return back()->with('status', 'Tim baru berhasil dibuat.');
    }

    public function destroy(Request $request, Team $team): RedirectResponse
    {
        abort_unless($team->isOwnedBy($request->user()), 404);

        $team->delete();

        return redirect()->route('dashboard')->with('status', 'Tim berhasil dihapus.');
    }

    public function update(UpdateTeamRequest $request, Team $team): RedirectResponse
    {
        abort_unless($team->isOwnedBy($request->user()), 404);

        $team->update($request->validated());

        return back()->with('status', 'Informasi tim berhasil diperbarui.');
    }
}
