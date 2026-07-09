<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'college_id',
        'name_ar',
        'name_en',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * العلاقة مع الكلية
     */
    public function college()
    {
        return $this->belongsTo(College::class);
    }

    /**
     * العلاقة مع التخصصات
     */
    public function specializations()
    {
        return $this->hasMany(Specialization::class);
    }

    /**
     * الحصول على اسم القسم (حسب اللغة)
     */
    public function getNameAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : ($this->name_en ?? $this->name_ar);
    }

    /**
     * نطاق البحث عن الأقسام النشطة
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
