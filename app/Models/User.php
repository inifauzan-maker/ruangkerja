<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'email', 'job_title', 'phone', 'bio', 'avatar_disk', 'avatar_path', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    public const RoleSuperAdmin = 'superadmin';

    public const RoleUser = 'user';

    protected $attributes = [
        'global_role' => self::RoleUser,
        'is_active' => true,
    ];

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'creator_id');
    }

    public function assignedTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class)->withTimestamps();
    }

    public function joinedTeams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function whatsappConnection(): HasOne
    {
        return $this->hasOne(WhatsappConnection::class);
    }

    public function adminAuditLogs(): HasMany
    {
        return $this->hasMany(AdminAuditLog::class, 'actor_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->global_role === self::RoleSuperAdmin;
    }

    /** @return list<string> */
    public static function globalRoles(): array
    {
        return [self::RoleUser, self::RoleSuperAdmin];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (User $user): void {
            if ($user->avatar_disk && $user->avatar_path) {
                Storage::disk($user->avatar_disk)->delete($user->avatar_path);
            }
        });
    }
}
