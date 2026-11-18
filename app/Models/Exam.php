<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    const TYPE_MIDTERM = 'midterm';
    const TYPE_FINAL = 'final';
    const TYPE_QUIZ = 'quiz';
    const TYPE_PRACTICAL = 'practical';
    const TYPES = [self::TYPE_MIDTERM, self::TYPE_FINAL, self::TYPE_QUIZ, self::TYPE_PRACTICAL];

    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_APPROVED = 'approved';
    const STATUS_PUBLISHED = 'published';
    const STATUS_REJECTED = 'rejected';
    const STATUSES = [self::STATUS_DRAFT, self::STATUS_SUBMITTED, self::STATUS_APPROVED, self::STATUS_PUBLISHED, self::STATUS_REJECTED];

    /** الحالات التي يُسمح فيها بتعديل الاختبار ورصد/تعديل الدرجات. */
    const EDITABLE_STATUSES = [self::STATUS_DRAFT, self::STATUS_REJECTED];

    protected $fillable = [
        'section_id', 'name', 'type', 'exam_date', 'start_time', 'duration_minutes', 'max_score', 'weight',
        'result_status', 'rejection_reason',
        'submitted_at', 'submitted_by', 'approved_at', 'approved_by', 'published_at', 'published_by',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'exam_date' => 'date:Y-m-d',
            'max_score' => 'decimal:2',
            'weight' => 'decimal:2',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    /** قائمة الطلاب المثبتة عند التقديم. */
    public function rosterEntries(): HasMany
    {
        return $this->hasMany(ExamRosterEntry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isEditable(): bool
    {
        return in_array($this->result_status, self::EDITABLE_STATUSES, true);
    }

    public function isPublished(): bool
    {
        return $this->result_status === self::STATUS_PUBLISHED;
    }

    public function scopePublished($query)
    {
        return $query->where('result_status', self::STATUS_PUBLISHED);
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            self::TYPE_MIDTERM => 'نصفي',
            self::TYPE_FINAL => 'نهائي',
            self::TYPE_QUIZ => 'اختبار قصير',
            self::TYPE_PRACTICAL => 'عملي',
            default => $type,
        };
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_DRAFT => 'مسودة',
            self::STATUS_SUBMITTED => 'مقدَّمة للمراجعة',
            self::STATUS_APPROVED => 'معتمدة',
            self::STATUS_PUBLISHED => 'منشورة',
            self::STATUS_REJECTED => 'مرفوضة',
            default => $status,
        };
    }
}
