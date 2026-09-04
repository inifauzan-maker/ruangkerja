<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfilePasswordRequest;
use Illuminate\Http\RedirectResponse;

class ProfilePasswordController extends Controller
{
    public function update(UpdateProfilePasswordRequest $request): RedirectResponse
    {
        $request->user()->update(['password' => $request->string('password')->toString()]);

        return back()->with('status', 'Password berhasil diperbarui.');
    }
}
