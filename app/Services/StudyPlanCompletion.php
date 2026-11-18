<?php

namespace App\Services;

use App\Models\AcademicSetting;
use App\Models\Course;
use App\Models\Level;
use App\Models\StudentProfile;
use App\Models\StudyPlan;

/**
 * هل أتمّ الطالب الخطة الدراسية لبرنامجه (دبلوم/بكالوريوس)؟
 * التعريف: كل مقرر إلزامي في خطة البرنامج (كل مستوياته) له نتيجة منشورة مكتملة وناجحة وفق نسبة النجاح
 * المضبوطة في الإعدادات. لا نسبة نجاح مضبوطة = لا يمكن الحكم (لا نفترض سياسة).
 */
class StudyPlanCompletion
{
    public function __construct(private AcademicRecordService $records)
    {
    }

    /**
     * @return array{eligible: bool, reason: ?string, message: ?string, missing: \Illuminate\Support\Collection}
     */
    public function check(StudentProfile $profile): array
    {
        if (! $profile->program_id) {
            return $this->no('no_program', 'لا يوجد برنامج دراسي مرتبط بملف الطالب.');
        }

        $required = StudyPlan::whereIn('level_id', Level::where('program_id', $profile->program_id)->select('id'))
            ->where('is_mandatory', true)
            ->pluck('course_id')->unique();

        if ($required->isEmpty()) {
            return $this->no('no_plan', 'لا توجد خطة دراسية بمقررات إلزامية لبرنامج الطالب.');
        }

        if (AcademicSetting::passPercentage() === null) {
            return $this->no('pass_percentage_missing', 'لم تُحدَّد نسبة النجاح في الإعدادات الأكاديمية؛ لا يمكن الحكم على إتمام الخطة.');
        }

        $record = $this->records->forStudent($profile);
        $passed = collect($record['terms'])->flatMap(fn ($t) => $t['courses'])
            ->filter(fn ($c) => $c['passed'] === true)
            ->map(fn ($c) => $c['course']->id)->unique();

        $missing = Course::whereIn('id', $required->diff($passed))->orderBy('code')->get();

        if ($missing->isNotEmpty()) {
            return ['eligible' => false, 'reason' => 'plan_incomplete', 'message' => 'لم تُكمل الخطة الدراسية: متبقٍ '.$missing->count().' مقرر إلزامي.', 'missing' => $missing];
        }

        return ['eligible' => true, 'reason' => null, 'message' => null, 'missing' => collect()];
    }

    private function no(string $reason, string $message): array
    {
        return ['eligible' => false, 'reason' => $reason, 'message' => $message, 'missing' => collect()];
    }
}
