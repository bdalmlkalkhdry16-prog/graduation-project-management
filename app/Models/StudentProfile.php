<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Phase 2 — Student Central Profile.
 */
class StudentProfile extends Model
{
    protected $fillable = [
        'user_id',
        'number_student',
        'specialization_id',
        'program_level',
        'level',
        'admission_year',
        'academic_status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class);
    }
}
