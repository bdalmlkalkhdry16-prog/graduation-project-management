<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'student_id',
        'employee_id',
        'phone',
        'profile_photo',
        'is_active',
        'specialization_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    // ========== العلاقات ==========

    /**
     * العلاقة مع التخصص
     */
    public function specialization()
    {
        return $this->belongsTo(Specialization::class);
    }

    /**
     * المشاريع التي يشرف عليها (للمشرفين)
     */
    public function supervisedProjects()
    {
        return $this->hasMany(Project::class, 'supervisor_id');
    }

    /**
     * عضوية المشاريع (للطلاب)
     */
    public function projectMemberships()
    {
        return $this->hasMany(ProjectMember::class, 'student_id');
    }
    public function ideas()
    {
        return $this->hasMany(Idea::class, 'student_id');
    }
    /**
     * المشاريع التي يشارك فيها الطالب (من خلال project_members)
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_members', 'student_id', 'project_id')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    /**
     * التعليقات التي كتبها المستخدم
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * الإشعارات الخاصة بالمستخدم
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * طلبات التطوير التي قدمها الطالب
     */
    public function developmentRequests()
    {
        return $this->hasMany(DevelopmentRequest::class, 'student_id');
    }

    /**
     * سجل النشاطات
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * التقييمات التي قام بها المشرف
     */
    public function evaluations()
    {
        return $this->hasMany(Evaluation::class, 'supervisor_id');
    }

    // ========== دوال مساعدة ==========

    /**
     * التحقق إذا كان المستخدم طالباً
     */
    public function isStudent()
    {
        return $this->role === 'student';
    }

    /**
     * التحقق إذا كان المستخدم مشرفاً
     */
    public function isSupervisor()
    {
        return $this->role === 'supervisor';
    }

    /**
     * التحقق إذا كان المستخدم مديراً
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * الحصول على اسم الدور بالعربية
     */
    public function getRoleNameAttribute()
    {
        return match($this->role) {
            'student' => 'طالب',
            'supervisor' => 'مشرف',
            'admin' => 'مدير النظام',
            default => 'غير محدد'
        };
    }

    // ========== Phase 1 — Roles & Permissions (نظام جديد، إضافي فقط) ==========
    //
    // كل ما يلي جديد بالكامل ولا يعدّل أو يستبدل role/isAdmin()/isSupervisor()/isStudent()
    // أعلاه. الشخص الواحد يمكن أن يحمل أكثر من Role عبر جدول user_roles.

    /**
     * الأدوار الجديدة التي يحملها المستخدم (نظام Roles & Permissions).
     */
    public function newRoles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot('department_id', 'assigned_at', 'assigned_by')
            ->withTimestamps();
    }

    /**
     * سجلات user_roles الخام (لعرض تفاصيل النطاق department_id لكل تعيين).
     */
    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }

    /**
     * هل يحمل المستخدم دورًا معينًا (بغض النظر عن القسم)؟
     */
    public function hasRole(string $slug): bool
    {
        return $this->newRoles()->where('slug', $slug)->exists();
    }

    /**
     * هل يملك المستخدم صلاحية معينة؟
     * إن مُرِّر $departmentId، تُطابَق الأدوار العامة (department_id = null)
     * أو الأدوار المحددة لنفس القسم فقط.
     */
    public function hasPermission(string $permissionSlug, ?int $departmentId = null): bool
    {
        $query = $this->userRoles()
            ->whereHas('role.permissions', fn ($q) => $q->where('slug', $permissionSlug));

        if ($departmentId !== null) {
            $query->where(function ($q) use ($departmentId) {
                $q->whereNull('department_id')->orWhere('department_id', $departmentId);
            });
        }

        return $query->exists();
    }

    // ========== Phase 2 — Student/Faculty/Staff Central Profiles (إضافي فقط) ==========

    /**
     * الملف الأكاديمي المركزي للطالب (إن وُجد).
     */
    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    /**
     * ملف عضو هيئة التدريس (إن وُجد). المشرف في نظام مشاريع التخرج
     * القديم (role = supervisor) هو نفس هذا الشخص.
     */
    public function facultyProfile(): HasOne
    {
        return $this->hasOne(FacultyProfile::class);
    }

    /**
     * ملف الموظف الإداري (إن وُجد).
     */
    public function staffProfile(): HasOne
    {
        return $this->hasOne(StaffProfile::class);
    }
}
