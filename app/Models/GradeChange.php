<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * سجل تعديلات الدرجات (المستخدم، التاريخ، القيمة السابقة والجديدة، السبب).
 */
class GradeChange extends Model
{
    const ACTION_ENTERED = 'entered';
    const ACTION_UPDATED = 'updated';
    const ACTION_CORRECTED = 'corrected';

    protected $fillable = ['grade_id', 'changed_by', 'action', 'old_score', 'new_score', 'old_status', 'new_status', 'reason'];

    protected function casts(): array
    {
        return ['old_score' => 'decimal:2', 'new_score' => 'decimal:2'];
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
