<?php

namespace App\Http\Controllers\Admin;

use App\Actions\UpdateAdminUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function update(UpdateUserRequest $request, User $user, UpdateAdminUser $updateAdminUser): RedirectResponse
    {
        $updatedUser = $updateAdminUser->execute(
            actor: $request->user(),
            targetUser: $user,
            attributes: [
                'global_role' => $request->string('global_role')->toString(),
                'is_active' => $request->boolean('is_active'),
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        if ($request->user()->is($updatedUser) && ! $updatedUser->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', 'Akun Anda telah dinonaktifkan.');
        }

        if ($request->user()->is($updatedUser) && ! $updatedUser->isSuperAdmin()) {
            return redirect()->route('dashboard')->with('status', 'Role superadmin Anda telah dilepas.');
        }

        return back()->with('status', "Akun {$updatedUser->name} berhasil diperbarui.");
    }
}
