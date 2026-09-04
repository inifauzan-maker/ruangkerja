<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $teams = Team::query()
            ->where(function ($query) use ($request): void {
                $query->where('owner_id', $request->user()->id)
                    ->orWhereHas('members', fn ($memberQuery) => $memberQuery->whereKey($request->user()->id));
            })
            ->with([
                'owner:id,name,email',
                'members:id,name,email',
                'boards' => fn ($query) => $query->withCount('lists')->orderBy('name'),
            ])
            ->orderBy('name')
            ->get();

        return view('dashboard', compact('teams'));
    }
}
