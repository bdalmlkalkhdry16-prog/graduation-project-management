<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'student_id',
        'role',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    // أدوار الأعضاء
    const ROLE_LEADER = 'leader';
    const ROLE_MEMBER = 'member';

    /**
     * العلاقة مع المشروع
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * العلاقة مع الطالب
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * التحقق إذا كان العضو هو قائد المشروع
     */
    public function isLeader()
    {
        return $this->role === self::ROLE_LEADER;
    }

    /**
     * الحصول على اسم الدور بالعربية
     */
    public function getRoleNameAttribute()
    {
        return match($this->role) {
            self::ROLE_LEADER => 'قائد الفريق',
            self::ROLE_MEMBER => 'عضو',
            default => 'غير محدد'
        };
    }
}
