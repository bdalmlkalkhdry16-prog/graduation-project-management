<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Specialization extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'name_ar',
        'name_en',
        'code',
        'description',
        'duration_years',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'duration_years' => 'integer',
    ];

    /**
     * العلاقة مع القسم
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * العلاقة مع المشاريع
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * العلاقة مع المستخدمين (الطلاب)
     */
    public function students()
    {
        return $this->hasMany(User::class);
    }

    /**
     * الحصول على اسم التخصص (حسب اللغة)
     */
    public function getNameAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : ($this->name_en ?? $this->name_ar);
    }

    /**
     * نطاق البحث عن التخصصات النشطة
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
