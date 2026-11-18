<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grade extends Model
{
    const STATUS_SCORED = 'scored';
    const STATUS_ABSENT_UNRESOLVED = 'absent_unresolved';

    protected $fillable = ['exam_id', 'enrollment_id', 'score', 'status', 'entered_by'];

    protected function casts(): array
    {
        return ['score' => 'decimal:2'];
    }

    public function isAbsentUnresolved(): bool
    {
        return $this->status === self::STATUS_ABSENT_UNRESOLVED;
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function changes(): HasMany
    {
        return $this->hasMany(GradeChange::class);
    }
}
