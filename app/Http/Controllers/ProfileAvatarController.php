<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileAvatarRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileAvatarController extends Controller
{
    public function show(Request $request): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user->avatar_disk && $user->avatar_path, 404);
        abort_unless(Storage::disk($user->avatar_disk)->exists($user->avatar_path), 404);

        return Storage::disk($user->avatar_disk)->response(
            $user->avatar_path,
            null,
            ['Cache-Control' => 'private, max-age=3600', 'X-Content-Type-Options' => 'nosniff'],
        );
    }

    public function update(UpdateProfileAvatarRequest $request): RedirectResponse
    {
        $user = $request->user();
        $oldDisk = $user->avatar_disk;
        $oldPath = $user->avatar_path;
        $path = $request->file('avatar')->store('avatars', 'local');

        $user->update(['avatar_disk' => 'local', 'avatar_path' => $path]);

        if ($oldDisk && $oldPath) {
            Storage::disk($oldDisk)->delete($oldPath);
        }

        return back()->with('status', 'Foto profil berhasil diperbarui.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user->avatar_disk && $user->avatar_path) {
            Storage::disk($user->avatar_disk)->delete($user->avatar_path);
        }
        $user->update(['avatar_disk' => null, 'avatar_path' => null]);

        return back()->with('status', 'Foto profil dihapus.');
    }
}
