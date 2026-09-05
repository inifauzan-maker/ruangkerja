<?php

namespace App\Console\Commands;

use App\Actions\UpdateAdminUser;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('users:promote-superadmin {email : Email pengguna} {--force : Lewati konfirmasi produksi}')]
#[Description('Promosikan pengguna aktif menjadi superadmin global')]
class PromoteSuperAdmin extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(UpdateAdminUser $updateAdminUser): int
    {
        $email = trim((string) $this->argument('email'));
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $this->error("Pengguna dengan email {$email} tidak ditemukan.");

            return self::FAILURE;
        }

        if (app()->isProduction() && ! $this->option('force') && ! $this->confirm("Promosikan {$user->email} menjadi superadmin?")) {
            $this->warn('Promosi dibatalkan.');

            return self::SUCCESS;
        }

        $updateAdminUser->execute(
            actor: null,
            targetUser: $user,
            attributes: ['global_role' => User::RoleSuperAdmin, 'is_active' => true],
            action: 'user.promoted_by_command',
        );

        $this->info("{$user->email} sekarang merupakan superadmin aktif.");

        return self::SUCCESS;
    }
}
