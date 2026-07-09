<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'link',
        'icon',
        'is_read',
        'read_at',
        'data',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'data' => 'array',
    ];

    // أنواع الإشعارات
    const TYPE_INFO = 'info';
    const TYPE_SUCCESS = 'success';
    const TYPE_WARNING = 'warning';
    const TYPE_ERROR = 'error';

    /**
     * العلاقة مع المستخدم
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * تحديد الإشعار كمقروء
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * الحصول على اسم نوع الإشعار بالعربية
     */
    public function getTypeNameAttribute()
    {
        return match($this->type) {
            self::TYPE_INFO => 'معلومات',
            self::TYPE_SUCCESS => 'نجاح',
            self::TYPE_WARNING => 'تحذير',
            self::TYPE_ERROR => 'خطأ',
            default => 'معلومات'
        };
    }

    /**
     * الحصول على لون الإشعار (لـ CSS)
     */
    public function getTypeColorAttribute()
    {
        return match($this->type) {
            self::TYPE_INFO => 'blue',
            self::TYPE_SUCCESS => 'green',
            self::TYPE_WARNING => 'orange',
            self::TYPE_ERROR => 'red',
            default => 'blue'
        };
    }

    /**
     * نطاق البحث عن الإشعارات غير المقروءة
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * نطاق البحث عن الإشعارات المقروءة
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * نطاق البحث حسب النوع
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
