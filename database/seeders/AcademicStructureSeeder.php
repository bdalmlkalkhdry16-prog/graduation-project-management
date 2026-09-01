<?php

namespace Database\Seeders;

use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Level;
use App\Models\Program;
use App\Models\Section;
use App\Models\Specialization;
use App\Models\StudentProfile;
use App\Models\StudyPlan;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Phase 3 — Academic Structure.
 *
 * بيانات توضيحية/تجريبية فقط، بنفس روح ProjectSeeder/EvaluationSeeder
 * القديمين. مبنية فوق الأقسام والتخصصات التجريبية الموجودة أصلاً
 * (18 تخصصًا من SpecializationSeeder) — وهي بيانات عامة (هندسة/تقنية
 * معلومات/إدارة أعمال...) وليست بيانات كلية المجتمع عمران الحقيقية.
 * ملاحظة مهمة لصاحب المشروع في نهاية تقرير المرحلة حول هذه النقطة.
 *
 * Idempotent بالكامل (updateOrCreate / firstOrCreate).
 */
class AcademicStructureSeeder extends Seeder
{
    public function run(): void
    {
        $activeYear = AcademicYear::getActive();

        if (! $activeYear) {
            $this->command?->warn('لا توجد سنة أكاديمية نشطة — شغّل AcademicYearSeeder أولاً.');
            return;
        }

        $firstTerm = AcademicTerm::firstOrCreate([
            'academic_year_id' => $activeYear->id,
            'semester' => 'first',
        ]);

        AcademicTerm::firstOrCreate([
            'academic_year_id' => $activeYear->id,
            'semester' => 'second',
        ]);

        $demoFaculty = User::where('role', 'supervisor')->first();

        Specialization::all()->each(function (Specialization $specialization) use ($firstTerm, $demoFaculty) {
            // كل التخصصات التجريبية الحالية duration_years = 4 → بكالوريوس
            $program = Program::updateOrCreate(
                ['specialization_id' => $specialization->id],
                ['level' => 'bachelor', 'total_credit_hours' => $specialization->duration_years * 33]
            );

            for ($levelNumber = 1; $levelNumber <= $specialization->duration_years; $levelNumber++) {
                Level::firstOrCreate(
                    ['program_id' => $program->id, 'level_number' => $levelNumber],
                    ['name' => "المستوى {$levelNumber}"]
                );
            }

            $firstLevel = $program->levels()->where('level_number', 1)->first();

            // مقررين تجريبيين فقط لكل تخصص، مرتبطين بالمستوى الأول
            foreach ([1, 2] as $i) {
                $course = Course::firstOrCreate(
                    ['code' => "{$specialization->code}10{$i}"],
                    [
                        'specialization_id' => $specialization->id,
                        'name_ar' => "مقرر تجريبي {$i} - {$specialization->name_ar}",
                        'credit_hours' => 3,
                    ]
                );

                StudyPlan::firstOrCreate([
                    'level_id' => $firstLevel->id,
                    'course_id' => $course->id,
                ], ['is_mandatory' => true]);

                Section::firstOrCreate([
                    'course_id' => $course->id,
                    'academic_term_id' => $firstTerm->id,
                    'code' => 'أ',
                ], [
                    'faculty_id' => $demoFaculty?->id,
                    'capacity' => 30,
                ]);
            }
        });

        // ربط أي StudentProfile موجود مسبقًا (من Phase 2) ببرنامجه
        // المطابق حسب specialization_id — لا تخمين لمستوى الطالب الحالي
        // (current_level_id يبقى فارغًا، يُحدَّد لاحقًا يدويًا أو عبر
        // وحدة شؤون الطلاب).
        StudentProfile::whereNull('program_id')
            ->whereNotNull('specialization_id')
            ->get()
            ->each(function (StudentProfile $profile) {
                $program = Program::where('specialization_id', $profile->specialization_id)->first();

                if ($program) {
                    $profile->update(['program_id' => $program->id]);
                }
            });
    }
}
