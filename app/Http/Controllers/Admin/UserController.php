<?php

namespace App\Http\Controllers\Admin;

use App\Actions\CreateAdminUser;
use App\Actions\DeleteAdminUser;
use App\Actions\UpdateAdminUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserController extends Controller
{
    public function create(): View
    {
        return view('admin.user-form', ['user' => new User]);
    }

    public function store(StoreUserRequest $request, CreateAdminUser $createAdminUser): RedirectResponse
    {
        $attributes = $request->safe()->except(['password_confirmation']);
        $attributes['is_active'] = $request->boolean('is_active');
        $user = $createAdminUser->execute($request->user(), $attributes, $request->ip(), $request->userAgent());

        return redirect()->route('admin.dashboard')->with('status', "Akun {$user->name} berhasil ditambahkan.");
    }

    public function edit(User $user): View
    {
        return view('admin.user-form', compact('user'));
    }

    public function destroy(Request $request, User $user, DeleteAdminUser $deleteAdminUser): RedirectResponse
    {
        $deleteAdminUser->execute($request->user(), $user, $request->ip(), $request->userAgent());

        return redirect()->route('admin.dashboard')->with('status', 'Akun pengguna berhasil dihapus.');
    }

    public function update(UpdateUserRequest $request, User $user, UpdateAdminUser $updateAdminUser): RedirectResponse
    {
        $attributes = $request->safe()->except(['password_confirmation']);
        $attributes['is_active'] = $request->boolean('is_active');
        if (empty($attributes['password'])) {
            unset($attributes['password']);
        }

        $credentialsChanged = isset($attributes['password'])
            || (isset($attributes['email']) && $attributes['email'] !== $user->email);

        $updatedUser = $updateAdminUser->execute(
            actor: $request->user(),
            targetUser: $user,
            attributes: $attributes,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        if ($request->user()->is($updatedUser) && (! $updatedUser->is_active || $credentialsChanged)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', $updatedUser->is_active
                ? 'Identitas login diperbarui. Silakan masuk kembali.'
                : 'Akun Anda telah dinonaktifkan.');
        }

        if ($request->user()->is($updatedUser) && ! $updatedUser->isSuperAdmin()) {
            return redirect()->route('dashboard')->with('status', 'Role superadmin Anda telah dilepas.');
        }

        return back()->with('status', "Akun {$updatedUser->name} berhasil diperbarui.");
    }
}
