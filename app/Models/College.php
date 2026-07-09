<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class College extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'name_en',
        'description',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * العلاقة مع الأقسام
     */
    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    /**
     * الحصول على اسم الكلية (حسب اللغة)
     */
    public function getNameAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : ($this->name_en ?? $this->name_ar);
    }

    /**
     * نطاق البحث عن الكليات النشطة
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
