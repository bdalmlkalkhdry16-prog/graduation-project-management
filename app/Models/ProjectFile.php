<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'file_category',
        'version',
        'uploaded_by',
        'description',
        'is_approved',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'version' => 'integer',
        'is_approved' => 'boolean',
    ];

    // أنواع الملفات
    const CATEGORY_PROPOSAL = 'proposal';      // خطة المشروع
    const CATEGORY_REPORT = 'report';          // التقرير النهائي
    const CATEGORY_PRESENTATION = 'presentation'; // عرض تقديمي
    const CATEGORY_SOURCE_CODE = 'source_code';   // الكود المصدري
    const CATEGORY_POSTER = 'poster';          // ملصق
    const CATEGORY_OTHER = 'other';            // أخرى

    /**
     * العلاقة مع المشروع
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * العلاقة مع المستخدم الذي رفع الملف
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * الحصول على اسم الفئة بالعربية
     */
    public function getCategoryNameAttribute()
    {
        return match($this->file_category) {
            self::CATEGORY_PROPOSAL => 'خطة المشروع',
            self::CATEGORY_REPORT => 'التقرير النهائي',
            self::CATEGORY_PRESENTATION => 'عرض تقديمي',
            self::CATEGORY_SOURCE_CODE => 'الكود المصدري',
            self::CATEGORY_POSTER => 'ملصق',
            self::CATEGORY_OTHER => 'أخرى',
            default => 'غير محدد'
        };
    }

    /**
     * الحصول على حجم الملف بصيغة مقروءة
     */
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * نطاق البحث عن الملفات المقبولة فقط
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * نطاق البحث حسب الفئة
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('file_category', $category);
    }
}
