<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamRosterEntry;
use App\Models\Grade;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * دورة النتائج: draft → submitted → approved → published، مع rejected (بسبب).
 * خريطة الانتقالات الوحيدة هي المصدر الذي يُفرض على الجميع (حتى المدير).
 */
class ExamResultWorkflow
{
    const TRANSITIONS = [
        'submit' => [[Exam::STATUS_DRAFT, Exam::STATUS_REJECTED], Exam::STATUS_SUBMITTED],
        'approve' => [[Exam::STATUS_SUBMITTED], Exam::STATUS_APPROVED],
        'reject' => [[Exam::STATUS_SUBMITTED, Exam::STATUS_APPROVED], Exam::STATUS_REJECTED],
        'publish' => [[Exam::STATUS_APPROVED], Exam::STATUS_PUBLISHED],
    ];

    public function __construct(private GradeService $grades)
    {
    }

    public function submit(Exam $exam, User $user): Exam
    {
        return $this->transition($exam, 'submit', $user, function (Exam $exam) use ($user) {
            $this->assertWeightsUsable($exam);

            $enrollmentIds = $this->grades->roster($exam)->pluck('id');

            if ($enrollmentIds->isEmpty()) {
                $this->fail('لا يوجد طلاب بتسجيل نشط في الشعبة لتقديم نتائجهم.');
            }

            // كل طالب في القائمة يجب أن يكون له رصد صريح: درجة، أو غياب مسجَّل (غير محسوم)
            $recorded = $exam->grades()->whereIn('enrollment_id', $enrollmentIds)
                ->where(fn ($q) => $q->whereNotNull('score')->orWhere('status', Grade::STATUS_ABSENT_UNRESOLVED))
                ->count();

            if ($recorded < $enrollmentIds->count()) {
                $this->fail('يجب رصد درجة (أو تسجيل غياب) لكل طالب بتسجيل نشط قبل التقديم (المتبقي: '.($enrollmentIds->count() - $recorded).').');
            }

            // تثبيت القائمة: من هنا تعتمد المراجعة والاعتماد والنشر على هذه القائمة وحدها
            $exam->rosterEntries()->delete();
            foreach ($enrollmentIds as $id) {
                ExamRosterEntry::create(['exam_id' => $exam->id, 'enrollment_id' => $id, 'frozen_by' => $user->id]);
            }

            return ['submitted_at' => now(), 'submitted_by' => $user->id, 'rejection_reason' => null];
        });
    }

    public function approve(Exam $exam, User $user): Exam
    {
        return $this->transition($exam, 'approve', $user, function (Exam $exam) use ($user) {
            $this->assertWeightsUsable($exam);
            $this->assertRosterIntegrity($exam);

            return ['approved_at' => now(), 'approved_by' => $user->id];
        });
    }

    public function reject(Exam $exam, User $user, string $reason): Exam
    {
        return $this->transition($exam, 'reject', $user, function (Exam $exam) use ($reason) {
            // العودة إلى الرصد: تُفك القائمة المثبتة وتُثبَّت من جديد عند إعادة التقديم
            $exam->rosterEntries()->delete();

            return ['rejection_reason' => $reason, 'approved_at' => null, 'approved_by' => null];
        }, ['reason' => $reason]);
    }

    public function publish(Exam $exam, User $user): Exam
    {
        return $this->transition($exam, 'publish', $user, function (Exam $exam) use ($user) {
            $this->assertWeightsUsable($exam);
            $this->assertRosterIntegrity($exam);

            $unresolved = $exam->grades()
                ->whereIn('enrollment_id', $exam->rosterEntries()->select('enrollment_id'))
                ->where('status', Grade::STATUS_ABSENT_UNRESOLVED)
                ->count();

            if ($unresolved > 0) {
                $this->fail("لا يمكن نشر النتائج: توجد حالات غياب غير محسومة ($unresolved). تُحسم بالتصحيح المعتمد (إدخال درجة) فقط.");
            }

            return ['published_at' => now(), 'published_by' => $user->id];
        });
    }

    /** لا حساب لنتائج الشعبة إذا كان مجموع أوزان اختباراتها صفرًا. الأوزان لا يُشترط أن تساوي 100. */
    private function assertWeightsUsable(Exam $exam): void
    {
        if ((float) $exam->section->exams()->sum('weight') <= 0) {
            $this->fail('مجموع أوزان اختبارات الشعبة صفر؛ لا يمكن حساب النتيجة. حدِّد وزنًا موجبًا لاختبار واحد على الأقل.');
        }
    }

    /**
     * سلامة الدورة بين التقديم والنشر: القائمة المثبتة موجودة، وكل عضو فيها تابع لشعبة الاختبار،
     * وله درجة ضمن النطاق (أو غياب غير محسوم يُمنع عند النشر)، ولا درجات لغير أعضائها.
     */
    private function assertRosterIntegrity(Exam $exam): void
    {
        $roster = $exam->rosterEntries()->pluck('enrollment_id');

        if ($roster->isEmpty()) {
            $this->fail('القائمة المثبتة للطلاب غير موجودة؛ أعد النتائج للرصد وقدِّمها من جديد.');
        }

        $foreign = Enrollment::whereIn('id', $roster)->where('section_id', '!=', $exam->section_id)->count();
        if ($foreign > 0) {
            $this->fail('القائمة المثبتة تحتوي تسجيلًا لا يتبع شعبة هذا الاختبار.');
        }

        $grades = $exam->grades()->get()->keyBy('enrollment_id');

        foreach ($roster as $enrollmentId) {
            $grade = $grades->get($enrollmentId);

            if (! $grade || ($grade->score === null && ! $grade->isAbsentUnresolved())) {
                $this->fail('طالب في القائمة المثبتة بلا درجة؛ أعد النتائج للرصد.');
            }

            if ($grade->score !== null && ((float) $grade->score < 0 || (float) $grade->score > (float) $exam->max_score)) {
                $this->fail('توجد درجة خارج النطاق المسموح.');
            }
        }
    }

    private function transition(Exam $exam, string $action, User $user, \Closure $extra, array $context = []): Exam
    {
        return DB::transaction(function () use ($exam, $action, $user, $extra, $context) {
            $exam = Exam::whereKey($exam->id)->lockForUpdate()->firstOrFail();
            [$from, $to] = self::TRANSITIONS[$action];

            if (! in_array($exam->result_status, $from, true)) {
                $this->fail('لا يمكن تنفيذ هذا الإجراء والنتائج في حالة "'.Exam::statusLabel($exam->result_status).'".');
            }

            $attributes = $extra($exam) + ['result_status' => $to];
            $old = $exam->result_status;
            $exam->update($attributes);

            ActivityLog::log($user->id, 'exam_results.'.$action, Exam::class, $exam->id, ['status' => $old], ['status' => $to] + $context);

            return $exam;
        });
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['exam' => $message]);
    }
}
