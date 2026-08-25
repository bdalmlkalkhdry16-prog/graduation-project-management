<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Phase 1 — Roles & Permissions.
 *
 * جزء من نظام الصلاحيات الجديد. لا علاقة له بعمود users.role القديم،
 * ولا يُستخدم حاليًا من قِبل أي Middleware أو Route قديم.
 */
class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->withPivot('department_id', 'assigned_at', 'assigned_by')
            ->withTimestamps();
    }
}
