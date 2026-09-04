<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBoardListRequest;
use App\Models\Board;
use Illuminate\Http\RedirectResponse;

class BoardListController extends Controller
{
    public function store(StoreBoardListRequest $request, Board $board): RedirectResponse
    {
        abort_unless($board->belongsToUser($request->user()), 404);

        $board->lists()->create([
            ...$request->validated(),
            'color' => 'violet',
            'position' => ((int) $board->lists()->max('position')) + 1,
        ]);

        return back()->with('status', 'List baru berhasil dibuat.');
    }
}
