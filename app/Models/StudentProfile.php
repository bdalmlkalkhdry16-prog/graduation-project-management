<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Phase 2 — Student Central Profile.
 * program_id / current_level_id: أُضيفا في Phase 3 (Academic Structure).
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
        'program_id',
        'current_level_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function currentLevel(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'current_level_id');
    }
}
