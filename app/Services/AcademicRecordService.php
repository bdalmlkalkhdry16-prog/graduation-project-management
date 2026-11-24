<?php

namespace App\Services;

use App\Models\AcademicSetting;
use App\Models\Enrollment;
use App\Models\ExamRosterEntry;
use App\Models\StudentProfile;

/**
 * Phase 7 — السجل الأكاديمي الرسمي.
 *
 * يعتمد حصرًا على الاختبارات المنشورة (published) ودرجاتها. لا افتراضات أكاديمية:
 *  - النسبة المئوية: مجموع (الدرجة ÷ الدرجة النهائية × الدرجة المخصصة) ÷ مجموع الدرجات المخصصة لاختبارات الشعبة،
 *    وتُحسب فقط عندما تُنشر كل اختبارات الشعبة ولكل منها درجة للطالب.
 *  - التقدير والمعدل: فقط عند ضبط سلم التقديرات في الإعدادات.
 *  - النجاح/الرسوب: فقط عند ضبط نسبة النجاح في الإعدادات.
 *  - الأوزان مرنة: لا يُشترط أن يساوي مجموعها 100؛ النسبة = Σ(مساهمات موزونة) ÷ Σ الأوزان × 100.
 *    وإن كان مجموع الأوزان صفرًا لا تُحسب نسبة (weights_zero).
 *  - النتائج المنشورة تبقى في سجل الطالب حتى لو صار تسجيله dropped؛ لا حذف ولا استبعاد للتاريخ.
 *    ولا تُعتمد درجة إلا لطالب ضمن القائمة المثبتة للاختبار وقت التقديم.
 */
class AcademicRecordService
{
    /**
     * @param  bool  $withholdUnpaid  للعرض على الطالب نفسه: تُستبعد نتائج السنوات غير المسددة رسومها
     *                                (فلا تتسرّب عبر المعدل أيضًا). الموظفون وحسابات الإتمام يستخدمون false.
     */
    public function forStudent(StudentProfile $profile, bool $withholdUnpaid = false): array
    {
        $withheld = $withholdUnpaid ? app(FeeStatus::class)->withheldYears($profile) : collect();
        $scale = AcademicSetting::gradeScale();
        $passPercentage = AcademicSetting::passPercentage();

        $enrollments = $profile->enrollments()
            ->with(['section.course', 'section.academicTerm.academicYear', 'section.exams', 'grades'])
            ->get();

        // الاختبارات التي ثُبِّت فيها كل تسجيل (قائمة التقديم)
        if ($withheld->isNotEmpty()) {
            $enrollments = $enrollments->reject(fn (Enrollment $e) => $withheld->contains('id', $e->section->academicTerm?->academic_year_id))->values();
        }

        $rosterExamIds = ExamRosterEntry::whereIn('enrollment_id', $enrollments->pluck('id'))
            ->get()->groupBy('enrollment_id')->map->pluck('exam_id');

        // المنسحب (dropped) يبقى في السجل فقط بما نُشر له من نتائج ضمن القائمة المثبتة
        $enrollments = $enrollments->filter(function (Enrollment $e) use ($rosterExamIds) {
            if ($e->status !== Enrollment::STATUS_DROPPED) {
                return true;
            }

            $ids = $rosterExamIds->get($e->id, collect());

            return $e->section->exams->filter->isPublished()->pluck('id')->intersect($ids)->isNotEmpty();
        })->each(fn (Enrollment $e) => $e->setRelation('rosterExamIds', $rosterExamIds->get($e->id, collect())));

        $courses = $enrollments->map(fn (Enrollment $e) => $this->courseRow($e, $scale, $passPercentage));

        $terms = $courses
            ->groupBy(fn ($c) => $c['term']->id)
            ->map(function ($rows) {
                $term = $rows->first()['term'];

                return [
                    'term' => $term,
                    'label' => ($term->academicYear->name ?? '').' — '.self::semesterLabel($term->semester),
                    'sort' => [$term->academicYear->year ?? 0, ['first' => 1, 'second' => 2, 'summer' => 3][$term->semester] ?? 9],
                    'courses' => $rows->sortBy(fn ($r) => $r['course']->code)->values()->all(),
                    'gpa' => $this->gpa($rows),
                ];
            })
            ->sortBy('sort')
            ->values()
            ->all();

        return [
            'student' => $profile->loadMissing(['user', 'program.specialization', 'currentLevel']),
            'terms' => $terms,
            'cumulative_gpa' => $this->gpa($courses),
            'has_scale' => $scale !== [],
            'pass_percentage' => $passPercentage,
            'issued_at' => now(),
            'withheld_years' => $withheld,
        ];
    }

    /** مجموع الساعات المعتمدة للمقررات الناجح فيها (كل مقرر مرة واحدة حتى لو أُعيد). */
    public function completedHours(StudentProfile $profile): float
    {
        return (float) collect($this->forStudent($profile)['terms'])->flatMap(fn ($t) => $t['courses'])
            ->filter(fn ($c) => $c['passed'] === true)
            ->unique(fn ($c) => $c['course']->id)
            ->sum(fn ($c) => (float) $c['credit_hours']);
    }

    /**
     * نتائج كل تسجيل غير منسحب في شعبة واحدة بنفس قواعد السجل الأكاديمي (للإحصاءات).
     *
     * @return \Illuminate\Support\Collection<int, array>
     */
    public function sectionOutcomes(\App\Models\Section $section): \Illuminate\Support\Collection
    {
        $scale = AcademicSetting::gradeScale();
        $passPercentage = AcademicSetting::passPercentage();
        $section->loadMissing('course', 'academicTerm.academicYear', 'exams');

        $enrollments = Enrollment::where('section_id', $section->id)->where('status', '!=', Enrollment::STATUS_DROPPED)
            ->with('grades')->get();
        $rosterExamIds = ExamRosterEntry::whereIn('enrollment_id', $enrollments->pluck('id'))->get()->groupBy('enrollment_id')->map->pluck('exam_id');

        return $enrollments->map(function (Enrollment $e) use ($section, $rosterExamIds, $scale, $passPercentage) {
            $e->setRelation('section', $section);
            $e->setRelation('rosterExamIds', $rosterExamIds->get($e->id, collect()));

            return $this->courseRow($e, $scale, $passPercentage);
        });
    }

    private function courseRow(Enrollment $enrollment, array $scale, ?float $passPercentage): array
    {
        $section = $enrollment->section;
        $allExams = $section->exams;
        $gradesByExam = $enrollment->grades->keyBy('exam_id');

        $rows = [];
        $total = 0.0;

        foreach ($allExams->filter->isPublished() as $exam) {
            $grade = $gradesByExam->get($exam->id);

            // لا تُحتسب درجة إلا لعضو في القائمة المثبتة لهذا الاختبار
            if (! $enrollment->rosterExamIds->contains($exam->id) || ! $grade || $grade->score === null) {
                continue;
            }

            $contribution = (float) $exam->max_score > 0 ? (float) $grade->score / (float) $exam->max_score * (float) $exam->weight : 0.0;
            $total += $contribution;
            $rows[] = ['exam' => $exam, 'score' => (float) $grade->score, 'contribution' => round($contribution, 2)];
        }

        $totalWeight = (float) $allExams->sum('weight');
        $complete = $allExams->isNotEmpty()
            && $allExams->every->isPublished()
            && count($rows) === $allExams->count();

        $weightsZero = $allExams->isNotEmpty() && $totalWeight <= 0;
        $percentage = $complete && ! $weightsZero ? round($total / $totalWeight * 100, 2) : null;

        $letter = $points = null;
        if ($percentage !== null) {
            foreach ($scale as $entry) {
                if ($percentage >= (float) $entry['min']) {
                    $letter = $entry['letter'];
                    $points = isset($entry['points']) && $entry['points'] !== '' ? (float) $entry['points'] : null;
                    break;
                }
            }
        }

        $passed = ($passPercentage !== null && $percentage !== null) ? $percentage >= $passPercentage : null;

        return [
            'enrollment' => $enrollment,
            'course' => $section->course,
            'section' => $section,
            'term' => $section->academicTerm,
            'credit_hours' => $section->course->credit_hours,
            'exams' => $rows,
            'total' => round($total, 2),
            'percentage' => $percentage,
            'complete' => $complete,
            'weights_zero' => $weightsZero,
            'dropped' => $enrollment->status === Enrollment::STATUS_DROPPED,
            'letter' => $letter,
            'points' => $points,
            'passed' => $passed,
            'status_label' => $passed === true ? 'ناجح' : ($passed === false ? 'راسب' : ($complete ? 'نتيجة مكتملة' : 'نتيجة غير مكتملة')),
            'result_date' => $complete ? $allExams->max('published_at') : null,
        ];
    }

    /** المعدل = Σ(نقاط×ساعات) ÷ Σ ساعات، للمقررات التي لها نقاط وساعات معتمدة فقط. */
    private function gpa($courses): ?float
    {
        $eligible = $courses->filter(fn ($c) => $c['points'] !== null && (float) $c['credit_hours'] > 0);
        $hours = (float) $eligible->sum(fn ($c) => (float) $c['credit_hours']);

        if ($hours <= 0) {
            return null;
        }

        return round($eligible->sum(fn ($c) => $c['points'] * (float) $c['credit_hours']) / $hours, 2);
    }

    public static function semesterLabel(?string $semester): string
    {
        return match ($semester) {
            'first' => 'الفصل الأول',
            'second' => 'الفصل الثاني',
            'summer' => 'الفصل الصيفي',
            default => (string) $semester,
        };
    }
}
