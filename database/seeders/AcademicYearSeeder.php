<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

/**
 * Phase 3 — Academic Structure.
 *
 * جدول academic_years قديم وموجود منذ النظام السابق (AcademicYearController)
 * لكنه لم يُزرَع بأي بيانات من قبل. هذا Seeder جديد بالكامل، لا يعدل أي
 * ملف قديم — فقط يضيف بيانات لجدول فارغ. Idempotent (updateOrCreate).
 */
class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        AcademicYear::query()->update(['is_active' => false]);

        AcademicYear::updateOrCreate(
            ['year' => 2026],
            [
                'name' => '2025-2026',
                'is_active' => true,
                'start_date' => '2025-09-01',
                'end_date' => '2026-06-30',
            ]
        );
    }
}
