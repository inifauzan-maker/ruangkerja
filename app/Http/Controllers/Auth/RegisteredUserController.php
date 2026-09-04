<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = DB::transaction(function () use ($validated): User {
            $user = User::create($validated);
            $team = $user->teams()->create([
                'name' => 'Tim '.$user->name,
                'slug' => Str::slug($user->name).'-'.Str::lower(Str::random(5)),
            ]);
            $board = $team->boards()->create(['name' => 'Proyek Pertama']);
            $defaultLists = [
                ['title' => 'To Do', 'color' => 'slate'],
                ['title' => 'Dikerjakan', 'color' => 'amber'],
                ['title' => 'Selesai', 'color' => 'emerald'],
                ['title' => 'Batal', 'color' => 'rose'],
            ];

            foreach ($defaultLists as $position => $list) {
                $board->lists()->create([...$list, 'position' => $position]);
            }

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
