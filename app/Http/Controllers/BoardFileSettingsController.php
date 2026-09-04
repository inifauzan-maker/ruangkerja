<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateBoardFileSettingsRequest;
use App\Models\Board;
use Illuminate\Http\RedirectResponse;

class BoardFileSettingsController extends Controller
{
    public function update(UpdateBoardFileSettingsRequest $request, Board $board): RedirectResponse
    {
        $board->load('team.members');
        abort_unless($board->team->canManageProjects($request->user()), 404);

        $board->update($request->validated());

        return back()->with('status', 'Izin download file diperbarui.');
    }
}
