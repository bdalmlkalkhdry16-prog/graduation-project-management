<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Section;
use App\Models\StudentProfile;
use App\Models\StudyPlan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Phase 6.1 — منطق تسجيل الطلاب في الشعب.
 *
 * ملاحظة: لا تُفرض سعة الشعبة (sections.capacity) هنا؛ العمود اختياري ولا توجد
 * قاعدة معتمدة لاستخدامه بعد، فيُعرض للاطلاع فقط.
 *
 * القاعدة الأكاديمية: الطالب مؤهَّل لشعبة إذا كان مقرر الشعبة ضمن الخطة الدراسية
 * (study_plans) لمستواه الحالي، وكان ذلك المستوى تابعًا لبرنامجه.
 */
class EnrollmentService
{
    /**
     * رسالة سبب عدم الأهلية، أو null إن كان الطالب مؤهَّلًا.
     */
    public function ineligibilityReason(StudentProfile $student, Section $section): ?string
    {
        if (! $student->program_id || ! $student->current_level_id) {
            return 'الطالب بلا برنامج أو مستوى حالي محدَّد.';
        }

        $level = $student->currentLevel;

        if (! $level || $level->program_id !== $student->program_id) {
            return 'مستوى الطالب لا ينتمي لبرنامجه.';
        }

        $inPlan = StudyPlan::where('level_id', $student->current_level_id)
            ->where('course_id', $section->course_id)
            ->exists();

        return $inPlan ? null : 'مقرر هذه الشعبة غير ضمن الخطة الدراسية لبرنامج الطالب ومستواه.';
    }

    public function activeCount(Section $section): int
    {
        return $section->enrollments()->active()->count();
    }

    /**
     * الطلاب المؤهَّلون وغير المسجَّلين بعد (بأي حالة) في الشعبة، مع بحث اختياري.
     */
    public function eligibleStudents(Section $section, ?string $search = null): Builder
    {
        $levelIds = StudyPlan::where('course_id', $section->course_id)->pluck('level_id');

        $query = StudentProfile::query()
            ->whereIn('current_level_id', $levelIds)
            ->whereNotNull('program_id')
            ->whereHas('currentLevel', fn ($q) => $q->whereColumn('levels.program_id', 'student_profiles.program_id'))
            ->whereDoesntHave('enrollments', fn ($q) => $q->where('section_id', $section->id))
            ->with(['user', 'program.specialization', 'currentLevel']);

        return $this->applySearch($query, $search)->orderBy('number_student');
    }

    /**
     * البحث بالاسم أو الرقم الأكاديمي.
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where('number_student', 'like', "%{$search}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
        });
    }

    /**
     * تسجيل طالب في شعبة (داخل معاملة). يرمي ValidationException عند المخالفة.
     */
    public function enroll(StudentProfile $student, Section $section): Enrollment
    {
        return DB::transaction(function () use ($student, $section) {
            $section = Section::whereKey($section->id)->lockForUpdate()->firstOrFail();

            if (Enrollment::where('student_profile_id', $student->id)->where('section_id', $section->id)->exists()) {
                $this->fail('student_profile_id', 'الطالب مسجَّل مسبقًا في هذه الشعبة. لتغيير وضعه عدّل حالة التسجيل الحالي.');
            }

            if ($reason = $this->ineligibilityReason($student, $section)) {
                $this->fail('student_profile_id', $reason);
            }

            return Enrollment::create([
                'student_profile_id' => $student->id,
                'section_id' => $section->id,
                'status' => Enrollment::STATUS_ACTIVE,
            ]);
        });
    }

    /**
     * تغيير حالة تسجيل (لا حذف أبدًا). إعادة التفعيل تتحقق من السعة.
     */
    public function changeStatus(Enrollment $enrollment, string $status): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $status) {
            $section = Section::whereKey($enrollment->section_id)->lockForUpdate()->firstOrFail();
            $enrollment = Enrollment::whereKey($enrollment->id)->lockForUpdate()->firstOrFail();

            $enrollment->update(['status' => $status]);

            return $enrollment;
        });
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
