<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Exam;
use App\Models\ExamDistributionChange;
use App\Models\Section;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * تعديل إداري لتوزيع درجات شعبة بعد تقديم نتائجها. التوزيع خاص بكل شعبة (لا إعداد عام)،
 * وكل تعديل يُسجَّل بسبب إلزامي وقيم سابقة وجديدة.
 * لا يمس إلا اختبارات الشعبة المعنية، ولا يعيد كتابة درجات أي شعبة أو فصل آخر.
 */
class ExamDistributionService
{
    /** حقول التوزيع التي تُسجَّل قيمها. */
    const FIELDS = ['name', 'type', 'max_score', 'weight'];

    public function update(Exam $exam, array $data, string $reason, User $user): Exam
    {
        return DB::transaction(function () use ($exam, $data, $reason, $user) {
            $exam = Exam::whereKey($exam->id)->lockForUpdate()->firstOrFail();
            $old = $this->snapshot($exam);
            $new = array_intersect_key($data, array_flip(self::FIELDS)) + $old;

            $highest = $exam->grades()->max('score');
            if ($highest !== null && (float) $new['max_score'] < (float) $highest) {
                throw ValidationException::withMessages(['max_score' => 'الدرجة النهائية أقل من درجة مرصودة فعلًا ('.($highest + 0).').']);
            }

            $exam->update($new);
            $this->log($exam->section_id, $exam->id, 'updated', $old, $this->snapshot($exam->fresh()), $reason, $user);

            return $exam->fresh();
        });
    }

    public function create(Section $section, array $data, string $reason, User $user): Exam
    {
        return DB::transaction(function () use ($section, $data, $reason, $user) {
            $exam = Exam::create([
                'section_id' => $section->id,
                'exam_date' => $data['exam_date'],
                'duration_minutes' => $data['duration_minutes'],
                'created_by' => $user->id,
            ] + array_intersect_key($data, array_flip(self::FIELDS)));

            $this->log($section->id, $exam->id, 'created', null, $this->snapshot($exam), $reason, $user);

            return $exam;
        });
    }

    public function delete(Exam $exam, string $reason, User $user): void
    {
        DB::transaction(function () use ($exam, $reason, $user) {
            $exam = Exam::whereKey($exam->id)->lockForUpdate()->firstOrFail();

            if ($exam->grades()->exists() || $exam->rosterEntries()->exists()) {
                throw ValidationException::withMessages(['exam' => 'لا يمكن حذف اختبار له درجات أو قائمة مثبتة.']);
            }

            $old = $this->snapshot($exam);
            $this->log($exam->section_id, $exam->id, 'deleted', $old, null, $reason, $user);
            $exam->delete();
        });
    }

    private function snapshot(Exam $exam): array
    {
        return [
            'name' => $exam->name,
            'type' => $exam->type,
            'max_score' => (float) $exam->max_score,
            'weight' => (float) $exam->weight,
        ];
    }

    private function log(int $sectionId, int $examId, string $action, ?array $old, ?array $new, string $reason, User $user): void
    {
        ExamDistributionChange::create([
            'section_id' => $sectionId, 'exam_id' => $examId, 'changed_by' => $user->id,
            'action' => $action, 'old_values' => $old, 'new_values' => $new, 'reason' => $reason,
        ]);

        ActivityLog::log($user->id, 'exam_distribution.'.$action, Exam::class, $examId, $old, ($new ?? []) + ['reason' => $reason]);
    }
}
