<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * تسجيل طالب في شعبة — المصدر الوحيد لمعرفة "من يدرس في أي شعبة".
 * فقط التسجيلات بحالة active تُعدّ عضوية فعلية (الجدول، الحضور).
 */
class Enrollment extends Model
{
    const STATUS_ACTIVE = 'active';
    const STATUS_DROPPED = 'dropped';
    const STATUS_COMPLETED = 'completed';

    const STATUSES = [self::STATUS_ACTIVE, self::STATUS_DROPPED, self::STATUS_COMPLETED];

    protected $fillable = ['student_profile_id', 'section_id', 'status', 'enrolled_at'];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'date:Y-m-d',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Enrollment $enrollment) {
            $enrollment->enrolled_at ??= now()->toDateString();
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
