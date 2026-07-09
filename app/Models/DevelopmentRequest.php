<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DevelopmentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'student_id',
        'reason',
        'proposed_improvements',
        'status',
        'admin_feedback',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    // حالات الطلب
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * العلاقة مع المشروع
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * العلاقة مع الطالب (مقدم الطلب)
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * العلاقة مع المدير الذي راجع الطلب
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * الموافقة على الطلب
     */
    public function approve($feedback = null)
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'admin_feedback' => $feedback,
            'reviewed_at' => now(),
        ]);
    }

    /**
     * رفض الطلب
     */
    public function reject($feedback = null)
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'admin_feedback' => $feedback,
            'reviewed_at' => now(),
        ]);
    }

    /**
     * الحصول على اسم الحالة بالعربية
     */
    public function getStatusNameAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'قيد الانتظار',
            self::STATUS_APPROVED => 'مقبول',
            self::STATUS_REJECTED => 'مرفوض',
            default => 'غير محدد'
        };
    }

    /**
     * نطاق البحث عن الطلبات المعلقة
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
}
