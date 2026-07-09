<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * العلاقة مع المستخدم
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * تسجيل نشاط جديد
     */
    public static function log($userId, $action, $modelType = null, $modelId = null, $oldValues = null, $newValues = null)
    {
        return self::create([
            'user_id' => $userId,
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * الحصول على اسم العملية بالعربية
     */
    public function getActionNameAttribute()
    {
        $actions = [
            'create' => 'إنشاء',
            'update' => 'تحديث',
            'delete' => 'حذف',
            'view' => 'عرض',
            'login' => 'تسجيل دخول',
            'logout' => 'تسجيل خروج',
            'upload' => 'رفع ملف',
            'download' => 'تحميل ملف',
            'evaluate' => 'تقييم',
            'approve' => 'موافقة',
            'reject' => 'رفض',
            'submit' => 'تقديم',
        ];

        return $actions[$this->action] ?? $this->action;
    }

    /**
     * نطاق البحث حسب نوع العملية
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * نطاق البحث حسب المودل
     */
    public function scopeByModel($query, $modelType, $modelId = null)
    {
        $query->where('model_type', $modelType);

        if ($modelId) {
            $query->where('model_id', $modelId);
        }

        return $query;
    }
}
