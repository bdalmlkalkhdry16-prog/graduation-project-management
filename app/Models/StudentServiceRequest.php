<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentServiceRequest extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_COMPLETED = 'completed';

    /** كتالوج الخدمات الإلكترونية (طلب تأجيل فصل محذوف بقرار). */
    const TYPE_ENROLLMENT_CERTIFICATE = 'enrollment_certificate';
    const TYPE_TRANSCRIPT = 'transcript';
    const TYPE_GRADUATION_STATEMENT = 'graduation_statement';
    const TYPE_RE_ENROLLMENT = 're_enrollment';
    const TYPE_GRADE_APPEAL = 'grade_appeal';

    const TYPES = [
        self::TYPE_ENROLLMENT_CERTIFICATE => 'شهادة قيد',
        self::TYPE_TRANSCRIPT => 'كشف درجات',
        self::TYPE_GRADUATION_STATEMENT => 'إفادة تخرج',
        self::TYPE_RE_ENROLLMENT => 'إعادة قيد',
        self::TYPE_GRADE_APPEAL => 'تظلم على درجة',
    ];

    /** أنواع مغلقة مؤقتًا (لا شيء حاليًا). */
    const NOT_YET_AVAILABLE = [];

    /** الحالات المفتوحة (لم تُحسم بعد). */
    const OPEN_STATUSES = [self::STATUS_PENDING, self::STATUS_IN_PROGRESS];

    /** الانتقالات المسموحة؛ rejected وcompleted نهائيتان. */
    const TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_IN_PROGRESS, self::STATUS_APPROVED, self::STATUS_REJECTED],
        self::STATUS_IN_PROGRESS => [self::STATUS_APPROVED, self::STATUS_REJECTED],
        self::STATUS_APPROVED => [self::STATUS_COMPLETED],
        self::STATUS_REJECTED => [],
        self::STATUS_COMPLETED => [],
    ];

    protected $fillable = [
        'enrollment_id',
        'student_profile_id',
        'type',
        'description',
        'status',
        'handled_by',
        'staff_notes',
    ];

    /** تسجيل المقرر المتظلَّم عليه (لطلبات التظلم فقط). */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * تسجيلات الطالب التي يحق له التظلم عليها: لها درجة منشورة ضمن القائمة المثبتة للاختبار
     * (يشمل المنسحب لاحقًا ما دامت نتائجه منشورة).
     */
    public static function appealableEnrollments(StudentProfile $profile)
    {
        return Enrollment::where('student_profile_id', $profile->id)
            ->whereHas('grades', fn ($g) => $g->whereNotNull('score')
                ->whereHas('exam', fn ($e) => $e->where('result_status', Exam::STATUS_PUBLISHED))
                ->whereIn('enrollment_id', ExamRosterEntry::select('enrollment_id')->whereColumn('exam_id', 'grades.exam_id')))
            ->with(['section.course', 'section.academicTerm.academicYear'])
            ->get();
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /** أنواع الطلبات المتاحة للتقديم حاليًا. */
    public static function availableTypes(): array
    {
        return array_diff_key(self::TYPES, array_flip(self::NOT_YET_AVAILABLE));
    }

    /** اسم النوع؛ الطلبات القديمة ذات النص الحر تُعرض كما كُتبت. */
    public static function typeLabel(?string $type): string
    {
        return self::TYPES[$type] ?? (string) $type;
    }

    /** الحالات التي يجوز الانتقال إليها من الحالة الحالية. */
    public function nextStatuses(): array
    {
        return self::TRANSITIONS[$this->status] ?? [];
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDING => 'قيد الانتظار',
            self::STATUS_IN_PROGRESS => 'قيد المعالجة',
            self::STATUS_APPROVED => 'مقبول',
            self::STATUS_REJECTED => 'مرفوض',
            self::STATUS_COMPLETED => 'مكتمل',
            default => $status,
        };
    }
}