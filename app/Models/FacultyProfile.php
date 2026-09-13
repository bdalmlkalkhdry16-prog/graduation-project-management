<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Phase 2 — Faculty Profile.
 */
class FacultyProfile extends Model
{
    protected $fillable = [
        'user_id',
        'specialization_id',
        'academic_rank',
        'hiring_year',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class);
    }
        /**
     * Phase 6 — Schedules. الشعب التي يدرّسها عضو هيئة التدريس.
     */
    public function sections(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Section::class, 'faculty_profile_id');
    }
}
