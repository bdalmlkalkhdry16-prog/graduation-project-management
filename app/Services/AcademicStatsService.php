<?php

namespace App\Services;

use App\Models\AcademicSetting;
use App\Models\AcademicTerm;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Section;
use App\Models\StudentFee;
use App\Models\StudentProfile;
use App\Models\StudentServiceRequest;
use Illuminate\Support\Collection;

/**
 * إحصاءات أكاديمية للمدير. لا تخترع نسبة نجاح: إن لم تُضبط في الإعدادات تُعرض النسب «غير محددة».
 */
class AcademicStatsService
{
    public function __construct(private AcademicRecordService $records)
    {
    }

    public function overview(): array
    {
        return [
            'students' => StudentProfile::count(),
            'active_enrollments' => Enrollment::where('status', Enrollment::STATUS_ACTIVE)->count(),
            'sections' => Section::count(),
            'exams_draft' => Exam::whereIn('result_status', [Exam::STATUS_DRAFT, Exam::STATUS_REJECTED])->count(),
            'exams_to_review' => Exam::where('result_status', Exam::STATUS_SUBMITTED)->count(),
            'exams_to_publish' => Exam::where('result_status', Exam::STATUS_APPROVED)->count(),
            'exams_published' => Exam::where('result_status', Exam::STATUS_PUBLISHED)->count(),
            'open_requests' => StudentServiceRequest::whereIn('status', [StudentServiceRequest::STATUS_PENDING, StudentServiceRequest::STATUS_IN_PROGRESS])->count(),
            'unsettled_fees' => StudentFee::with(['payments', 'discounts'])->get()->reject->isSettled()->count(),
            'pass_percentage' => AcademicSetting::passPercentage(),
        ];
    }

    /**
     * نتائج المقررات في فصل: لكل مقرر عدد المكتملة نتيجتهم والناجحين والراسبين.
     * «مكتملة» = كل اختبارات الشعبة منشورة ولكل طالب درجاتها. غير المكتملة لا تدخل في النسبة.
     *
     * @return Collection<int, array>
     */
    public function coursePassRates(?int $termId): Collection
    {
        if (! $termId) {
            return collect();
        }

        return Section::where('academic_term_id', $termId)->with('course')->get()
            ->groupBy('course_id')
            ->map(function (Collection $sections) {
                $rows = $sections->flatMap(fn (Section $s) => $this->records->sectionOutcomes($s));
                $complete = $rows->filter(fn ($r) => $r['percentage'] !== null);
                $passed = $complete->filter(fn ($r) => $r['passed'] === true)->count();
                $failed = $complete->filter(fn ($r) => $r['passed'] === false)->count();
                $judged = $passed + $failed;

                return [
                    'course' => $sections->first()->course,
                    'sections' => $sections->count(),
                    'enrolled' => $rows->count(),
                    'complete' => $complete->count(),
                    'incomplete' => $rows->count() - $complete->count(),
                    'passed' => $passed,
                    'failed' => $failed,
                    'pass_rate' => $judged > 0 ? round($passed / $judged * 100, 1) : null,
                ];
            })
            ->sortBy(fn ($r) => $r['course']->code)->values();
    }

    public function defaultTermId(): ?int
    {
        return AcademicTerm::with('academicYear')->get()
            ->sortByDesc(fn ($t) => [$t->academicYear->year ?? 0, ['first' => 1, 'second' => 2, 'summer' => 3][$t->semester] ?? 0])
            ->first()?->id;
    }
}
