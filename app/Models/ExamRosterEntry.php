<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * عضو في قائمة طلاب اختبار مثبتة عند التقديم.
 */
class ExamRosterEntry extends Model
{
    protected $fillable = ['exam_id', 'enrollment_id', 'frozen_by'];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }
}
