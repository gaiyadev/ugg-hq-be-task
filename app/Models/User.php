<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot('created_at');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class, 'created_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    // ─── Permission Helpers ───────────────────────────────────────────────

    /**
     * Check if user has a specific permission (via any assigned role).
     */
    public function hasPermission(string $permissionSlug): bool
    {
        return $this->roles()
            ->whereHas('permissions', fn($q) => $q->where('slug', $permissionSlug))
            ->exists();
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasRole(string|array $roleSlug): bool
    {
        $slugs = is_array($roleSlug) ? $roleSlug : [$roleSlug];
        return $this->roles()->whereIn('slug', $slugs)->exists();
    }

    /**
     * Get all flat permission slugs for this user.
     *
     * @return array<string>
     */
    public function getAllPermissions(): array
    {
        return $this->roles()
            ->with('permissions:id,slug')
            ->get()
            ->flatMap(fn(Role $role) => $role->permissions->pluck('slug'))
            ->unique()
            ->values()
            ->toArray();
    }
}
