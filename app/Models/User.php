<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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
}
