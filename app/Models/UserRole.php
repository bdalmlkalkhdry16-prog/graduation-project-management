<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Phase 1 — Roles & Permissions.
 *
 * Pivot Model صريح (وليس pivot ضمني) لأننا نحتاج حقولاً إضافية
 * (department_id لتحديد نطاق الدور، assigned_by لسجل من منح الدور).
 */
class UserRole extends Model
{
    protected $table = 'user_roles';

    protected $fillable = [
        'user_id',
        'role_id',
        'department_id',
        'assigned_at',
        'assigned_by',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
