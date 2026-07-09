<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title_ar',
        'title_en',
        'abstract_ar',
        'abstract_en',
        'keywords',
        'supervisor_id',
        'specialization_id',
        'status',
        'academic_year',
        'semester',
        'success_percentage',
        'feedback',
        'submission_date',
        'approval_date',
        'defense_date',
        'defense_location',
        'defense_notes',
        'idea_approved',
        'idea_submitted_at',
        'idea_review_notes',
    ];

    protected $casts = [
        'submission_date' => 'date',
        'approval_date' => 'date',
        'defense_date' => 'datetime',
        'success_percentage' => 'float',
        'academic_year' => 'integer',
        'idea_approved' => 'boolean',
        'idea_submitted_at' => 'datetime',
    ];

    // حالات المشروع
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_COMPLETED = 'completed';

    // الفصول الدراسية
    const SEMESTER_FIRST = 'first';
    const SEMESTER_SECOND = 'second';
    const SEMESTER_SUMMER = 'summer';

    // حالات الفكرة (مرتبطة بالفكرة القديمة – يمكن الاحتفاظ بها)
    const IDEA_STATUS_PENDING = 'pending';
    const IDEA_STATUS_APPROVED = 'approved';
    const IDEA_STATUS_REJECTED = 'rejected';

    // ========== العلاقات ==========
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function specialization()
    {
        return $this->belongsTo(Specialization::class);
    }

    public function members()
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'project_members', 'project_id', 'student_id')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    public function leader()
    {
        return $this->hasOne(ProjectMember::class)->where('role', 'leader')->with('student');
    }

    public function files()
    {
        return $this->hasMany(ProjectFile::class);
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    public function latestEvaluation()
    {
        return $this->hasOne(Evaluation::class)->latestOfMany();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function developmentRequests()
    {
        return $this->hasMany(DevelopmentRequest::class);
    }

    // ========== دوال الحساب ==========
    public function calculateSuccessPercentage()
{
    $evaluation = $this->latestEvaluation;

    if (!$evaluation) return null;

    $total = ($evaluation->creativity_score * 0.40) +
             ($evaluation->implementation_score * 0.30) +
             ($evaluation->documentation_score * 0.20) +
             ($evaluation->presentation_score * 0.10);

    $percentage = round($total, 2);
    $this->update(['success_percentage' => $percentage]);
    return $percentage;
}
    // ========== دوال المساعدة ==========
    public function getTitleAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->title_ar : ($this->title_en ?? $this->title_ar);
    }

    public function getAbstractAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->abstract_ar : ($this->abstract_en ?? $this->abstract_ar);
    }

    public function getStatusNameAttribute()
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'مسودة',
            self::STATUS_SUBMITTED => 'تم التقديم',
            self::STATUS_UNDER_REVIEW => 'قيد المراجعة',
            self::STATUS_APPROVED => 'مقبول',
            self::STATUS_REJECTED => 'مرفوض',
            self::STATUS_COMPLETED => 'مكتمل',
            default => 'غير محدد'
        };
    }

    public static function getStatusName($status)
    {
        return match($status) {
            self::STATUS_DRAFT => 'مسودة',
            self::STATUS_SUBMITTED => 'تم التقديم',
            self::STATUS_UNDER_REVIEW => 'قيد المراجعة',
            self::STATUS_APPROVED => 'مقبول',
            self::STATUS_REJECTED => 'مرفوض',
            self::STATUS_COMPLETED => 'مكتمل',
            default => $status,
        };
    }

    public function getSemesterNameAttribute()
    {
        return match($this->semester) {
            self::SEMESTER_FIRST => 'الفصل الأول',
            self::SEMESTER_SECOND => 'الفصل الثاني',
            self::SEMESTER_SUMMER => 'الفصل الصيفي',
            default => 'غير محدد'
        };
    }

    // ========== نطاقات البحث ==========
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED)
            ->orWhere('status', self::STATUS_COMPLETED);
    }

    public function scopeByAcademicYear($query, $year)
    {
        return $query->where('academic_year', $year);
    }

    // ========== دوال الأفكار (للتوافق القديم) ==========
    public static function checkDuplicateIdea($title_ar, $keywords = null)
    {
        return self::where('idea_approved', true)
            ->where(function ($q) use ($title_ar, $keywords) {
                $q->where('title_ar', 'like', "%$title_ar%");
                if ($keywords) {
                    $q->orWhere('keywords', 'like', "%$keywords%");
                }
            })
            ->exists();
    }
}