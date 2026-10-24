<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SsoUserService
{
    /** @param array{id: string, name: string, email: string} $identity */
    public function resolve(array $identity): User
    {
        return DB::transaction(function () use ($identity): User {
            $user = User::query()->where('sso_id', $identity['id'])->lockForUpdate()->first();
            $user ??= User::query()->where('email', $identity['email'])->lockForUpdate()->first();

            if ($user && (! $user->is_active || ($user->sso_id !== null && $user->sso_id !== $identity['id']))) {
                throw ValidationException::withMessages(['sso' => 'Akun tidak dapat digunakan untuk login SSO. Hubungi superadmin.']);
            }

            $isNew = $user === null;
            if ($isNew) {
                $user = new User;
                $user->name = $identity['name'];
                $user->email = $identity['email'];
                $user->password = Str::random(64);
                $user->global_role = User::RoleUser;
                $user->is_active = true;
            }

            $user->sso_id = $identity['id'];
            $user->save();

            if ($isNew) {
                $team = $user->teams()->create([
                    'name' => 'Tim '.$user->name,
                    'slug' => Str::slug($user->name).'-'.Str::lower(Str::random(8)),
                ]);
                $board = $team->boards()->create(['name' => 'Proyek Pertama']);
                foreach ([['title' => 'To Do', 'color' => 'slate'], ['title' => 'Dikerjakan', 'color' => 'amber'], ['title' => 'Selesai', 'color' => 'emerald'], ['title' => 'Batal', 'color' => 'rose']] as $position => $list) {
                    $board->lists()->create([...$list, 'position' => $position]);
                }
            }

            return $user;
        });
    }
}
