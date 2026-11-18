<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\GradeChange;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GradeService
{
    /**
     * قائمة طلاب الاختبار:
     *  - أثناء الرصد (draft/rejected): طلاب التسجيل النشط الحاليون في الشعبة.
     *  - بعد التقديم (submitted/approved/published): القائمة المثبتة وقت التقديم، مهما تغيّرت
     *    حالة التسجيل لاحقًا (بما فيها dropped).
     */
    public function roster(Exam $exam)
    {
        $query = Enrollment::where('section_id', $exam->section_id)->with(['studentProfile.user']);

        if ($exam->isEditable()) {
            $query->active();
        } else {
            $query->whereIn('id', $exam->rosterEntries()->select('enrollment_id'));
        }

        return $query->get()
            ->sortBy(fn ($e) => $e->studentProfile->number_student)
            ->values();
    }

    /**
     * حفظ درجات (مسودة). لا تُمسح درجة سابقة بقيمة فارغة؛ التعديل يتطلب قيمة صريحة،
     * وكل تغيير يُسجَّل في grade_changes. الغياب لا يتحول إلى صفر: يُسجَّل كحالة
     * "غياب غير محسوم" (score = null) ولا يمكن نشره.
     *
     * @param  array<int, mixed>  $scores  enrollment_id => score
     * @param  array<int, mixed>  $absent  enrollment_id => truthy لتسجيل غياب غير محسوم
     * @return int عدد الدرجات التي تغيّرت فعلًا
     */
    public function saveScores(Exam $exam, array $scores, User $user, array $absent = []): int
    {
        return DB::transaction(function () use ($exam, $scores, $user, $absent) {
            $exam = Exam::whereKey($exam->id)->lockForUpdate()->firstOrFail();

            if (! $exam->isEditable()) {
                throw ValidationException::withMessages(['exam' => 'نتائج هذا الاختبار مقفلة (حالتها: '.Exam::statusLabel($exam->result_status).'). التعديل غير متاح.']);
            }

            $absentIds = collect($absent)->filter(fn ($v) => filter_var($v, FILTER_VALIDATE_BOOLEAN))->keys()->map(fn ($k) => (int) $k)->all();
            $entered = collect($scores)->filter(fn ($v) => $v !== null && $v !== '')->all();

            if (array_intersect(array_map('intval', array_keys($entered)), $absentIds) !== []) {
                throw ValidationException::withMessages(['scores' => 'لا يمكن رصد درجة وتسجيل غياب للطالب نفسه في الوقت نفسه.']);
            }

            $this->assertEnrollmentsAllowed($exam, array_merge(array_map('intval', array_keys($entered)), $absentIds));

            $changed = 0;

            foreach ($entered as $enrollmentId => $value) {
                $new = round((float) $value, 2);

                if ($new < 0 || $new > (float) $exam->max_score) {
                    throw ValidationException::withMessages(['scores' => 'الدرجة خارج النطاق المسموح (0 – '.($exam->max_score + 0).').']);
                }

                $changed += $this->store($exam, (int) $enrollmentId, $new, Grade::STATUS_SCORED, $user);
            }

            foreach ($absentIds as $enrollmentId) {
                $changed += $this->store($exam, $enrollmentId, null, Grade::STATUS_ABSENT_UNRESOLVED, $user);
            }

            return $changed;
        });
    }

    /**
     * تصحيح معتمد لدرجة بعد التقديم/الاعتماد/النشر — بسبب إلزامي وتسجيل كامل.
     * هو الطريق الوحيد لحسم "غياب غير محسوم" (بإدخال درجة صريحة).
     */
    public function correct(Grade $grade, float $newScore, string $reason, User $user): Grade
    {
        return DB::transaction(function () use ($grade, $newScore, $reason, $user) {
            $exam = Exam::whereKey($grade->exam_id)->lockForUpdate()->firstOrFail();

            if (! in_array($exam->result_status, [Exam::STATUS_APPROVED, Exam::STATUS_PUBLISHED], true)) {
                throw ValidationException::withMessages(['exam' => 'التصحيح المعتمد متاح للنتائج المعتمدة أو المنشورة فقط.']);
            }

            // الدرجة يجب أن تعود لطالب في القائمة المثبتة (وبالتالي في شعبة الاختبار)
            $this->assertEnrollmentsAllowed($exam, [$grade->enrollment_id]);

            $new = round($newScore, 2);
            if ($new < 0 || $new > (float) $exam->max_score) {
                throw ValidationException::withMessages(['new_score' => 'الدرجة خارج النطاق المسموح (0 – '.($exam->max_score + 0).').']);
            }

            $grade = Grade::whereKey($grade->id)->lockForUpdate()->firstOrFail();
            $old = $grade->score;
            $oldStatus = $grade->status;

            $grade->update(['score' => $new, 'status' => Grade::STATUS_SCORED, 'entered_by' => $user->id]);

            GradeChange::create([
                'grade_id' => $grade->id,
                'changed_by' => $user->id,
                'action' => GradeChange::ACTION_CORRECTED,
                'old_score' => $old,
                'new_score' => $new,
                'old_status' => $oldStatus,
                'new_status' => Grade::STATUS_SCORED,
                'reason' => $reason,
            ]);

            ActivityLog::log($user->id, 'grade.corrected', Grade::class, $grade->id, ['score' => $old, 'status' => $oldStatus], ['score' => $new, 'status' => Grade::STATUS_SCORED, 'reason' => $reason]);

            return $grade;
        });
    }

    /**
     * كل enrollment_id يجب أن يتبع شعبة الاختبار فعلًا، وأن يكون ضمن مجموعة الطلاب المسموح لها:
     * التسجيل النشط أثناء الرصد، أو القائمة المثبتة بعد التقديم.
     */
    public function assertEnrollmentsAllowed(Exam $exam, array $enrollmentIds): void
    {
        $ids = array_values(array_unique(array_map('intval', $enrollmentIds)));

        if ($ids === []) {
            return;
        }

        $inSection = Enrollment::where('section_id', $exam->section_id)->whereIn('id', $ids)->pluck('id')->all();

        if (array_diff($ids, $inSection) !== []) {
            throw ValidationException::withMessages(['scores' => 'تسجيل لا يتبع شعبة هذا الاختبار.']);
        }

        if ($exam->isEditable()) {
            $active = Enrollment::where('section_id', $exam->section_id)->active()->whereIn('id', $ids)->pluck('id')->all();

            if (array_diff($ids, $active) !== []) {
                throw ValidationException::withMessages(['scores' => 'لا يمكن رصد درجة لطالب غير مسجَّل (تسجيل نشط) في شعبة هذا الاختبار.']);
            }

            return;
        }

        $frozen = $exam->rosterEntries()->whereIn('enrollment_id', $ids)->pluck('enrollment_id')->all();

        if (array_diff($ids, $frozen) !== []) {
            throw ValidationException::withMessages(['scores' => 'الطالب ليس ضمن القائمة المثبتة لهذا الاختبار.']);
        }
    }

    /** يحفظ سجل درجة واحدًا مع سجل التغيير؛ يعيد 1 إن تغيّر شيء و0 إن لم يتغير. */
    private function store(Exam $exam, int $enrollmentId, ?float $score, string $status, User $user): int
    {
        $grade = Grade::firstOrNew(['exam_id' => $exam->id, 'enrollment_id' => $enrollmentId]);
        $oldScore = $grade->exists ? $grade->score : null;
        $oldStatus = $grade->exists ? $grade->status : null;

        if ($grade->exists
            && $oldStatus === $status
            && (($oldScore === null && $score === null) || ($oldScore !== null && $score !== null && (float) $oldScore === $score))) {
            return 0;
        }

        $grade->score = $score;
        $grade->status = $status;
        $grade->entered_by = $user->id;
        $grade->save();

        GradeChange::create([
            'grade_id' => $grade->id,
            'changed_by' => $user->id,
            'action' => $oldScore === null && $oldStatus === null ? GradeChange::ACTION_ENTERED : GradeChange::ACTION_UPDATED,
            'old_score' => $oldScore,
            'new_score' => $score,
            'old_status' => $oldStatus,
            'new_status' => $status,
        ]);

        return 1;
    }
}
