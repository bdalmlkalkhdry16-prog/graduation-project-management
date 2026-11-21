<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\StudentFee;
use App\Models\StudentProfile;
use Illuminate\Support\Collection;

/**
 * حجب النتائج عن الطالب عند عدم سداد رسوم السنة الدراسية.
 * القاعدة: نتائج السنة التي لها رسم مولَّد وغير مسدد بالكامل تُحجب عن الطالب نفسه.
 * لا رسم مولَّد = لا حجب. الحجب على الطالب فقط؛ الموظفون والمدرسون وحسابات الإتمام لا تتأثر.
 */
class FeeStatus
{
    /** @return Collection<int, AcademicYear> السنوات المحجوبة نتائجها. */
    public function withheldYears(StudentProfile $profile): Collection
    {
        return $profile->studentFees()->with(['academicYear', 'payments'])->get()
            ->reject(fn (StudentFee $fee) => $fee->isSettled())
            ->map->academicYear
            ->unique('id')
            ->sortBy('year')
            ->values();
    }
}
