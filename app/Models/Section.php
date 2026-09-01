<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Section extends Model
{
    protected $fillable = [
        'course_id',
        'academic_term_id',
        'faculty_profile_id',
        'code',
        'capacity',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function academicTerm(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    /**
     * عضو هيئة التدريس المسؤول عن الشعبة، عبر faculty_profiles
     * (وليس users.id مباشرة) — بهذا يفرض قاعدة البيانات نفسها أن
     * المُدرِّس له ملف أكاديمي فعلي (Phase 2). للوصول لحساب المستخدم
     * الأساسي: $section->faculty?->user.
     */
    public function faculty(): BelongsTo
    {
        return $this->belongsTo(FacultyProfile::class, 'faculty_profile_id');
    }
}
