<?php

namespace Tests\Support;

use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamRosterEntry;
use App\Models\FacultyProfile;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Permission;
use App\Models\Program;
use App\Models\Role;
use App\Models\Section;
use App\Models\Specialization;
use App\Models\StudentProfile;
use App\Models\StudyPlan;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

/**
 * بيانات أساسية مشتركة لاختبارات Phase 7.
 */
trait AcademicFixtures
{
    protected Specialization $spec;
    protected Program $program;
    protected Level $level;
    protected AcademicTerm $term;
    protected Course $course;
    protected User $facultyUser;
    protected FacultyProfile $faculty;
    protected Section $section;
    private int $fixtureSeq = 0;

    protected function setUpAcademic(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->spec = Specialization::factory()->create();
        $this->program = Program::create(['specialization_id' => $this->spec->id, 'level' => 'bachelor']);
        $this->level = Level::create(['program_id' => $this->program->id, 'level_number' => 1, 'name' => 'المستوى الأول']);
        $year = AcademicYear::create(['name' => '2025-2026', 'year' => 2026]);
        $this->term = AcademicTerm::create(['academic_year_id' => $year->id, 'semester' => 'first']);
        $this->course = $this->makeCourse('C1', 3);

        [$this->facultyUser, $this->faculty] = $this->makeFaculty();
        $this->section = $this->makeSection($this->faculty);
    }

    protected function userWithRole(string $slug, string $state = 'supervisor'): User
    {
        $user = User::factory()->{$state}()->create();
        $user->newRoles()->attach(Role::where('slug', $slug)->first());

        return $user;
    }

    /** مستخدم بدور مخصص يحمل صلاحيات محددة فقط. */
    protected function userWithPermissions(array $slugs): User
    {
        $role = Role::create(['slug' => 'custom'.uniqid(), 'name' => 'مخصص', 'is_system' => false]);
        $role->permissions()->sync(Permission::whereIn('slug', $slugs)->pluck('id'));
        $user = User::factory()->supervisor()->create();
        $user->newRoles()->attach($role);

        return $user;
    }

    protected function makeCourse(string $code, int $hours = 3): Course
    {
        $course = Course::create(['specialization_id' => $this->spec->id, 'code' => $code, 'name_ar' => 'مقرر '.$code, 'credit_hours' => $hours]);
        StudyPlan::create(['level_id' => $this->level->id, 'course_id' => $course->id]);

        return $course;
    }

    protected function makeFaculty(): array
    {
        $user = $this->userWithRole('faculty');

        return [$user, FacultyProfile::create(['user_id' => $user->id])];
    }

    protected function makeSection(FacultyProfile $faculty, ?Course $course = null): Section
    {
        return Section::create([
            'course_id' => ($course ?? $this->course)->id, 'academic_term_id' => $this->term->id,
            'faculty_profile_id' => $faculty->id, 'code' => 'S'.++$this->fixtureSeq, 'capacity' => 30,
        ]);
    }

    protected function makeStudent(?string $name = null): array
    {
        $user = $this->userWithRole('student', 'student');
        if ($name) {
            $user->update(['name' => $name]);
        }
        $profile = StudentProfile::create([
            'user_id' => $user->id, 'number_student' => 'N'.uniqid(), 'specialization_id' => $this->spec->id,
            'program_id' => $this->program->id, 'current_level_id' => $this->level->id,
        ]);

        return [$user->fresh(), $profile];
    }

    protected function enroll(StudentProfile $student, ?Section $section = null, string $status = 'active'): Enrollment
    {
        return Enrollment::create(['student_profile_id' => $student->id, 'section_id' => ($section ?? $this->section)->id, 'status' => $status]);
    }

    protected function makeExam(?Section $section = null, array $attrs = []): Exam
    {
        return Exam::create($attrs + [
            'section_id' => ($section ?? $this->section)->id, 'name' => 'اختبار '.++$this->fixtureSeq, 'type' => 'midterm',
            'exam_date' => '2026-11-01', 'duration_minutes' => 60, 'max_score' => 100, 'weight' => 40,
        ]);
    }

    protected function setGrade(Exam $exam, Enrollment $enrollment, float $score): Grade
    {
        $grade = Grade::create(['exam_id' => $exam->id, 'enrollment_id' => $enrollment->id, 'score' => $score]);

        // اختبار مقفل أصلًا: الطالب ضمن القائمة المثبتة كما لو ثُبِّت عند التقديم
        if (! $exam->fresh()->isEditable()) {
            ExamRosterEntry::firstOrCreate(['exam_id' => $exam->id, 'enrollment_id' => $enrollment->id]);
        }

        return $grade;
    }

    /** يغيّر حالة النتائج مباشرة (بدون الخدمة) مع تثبيت/فك القائمة بما يوافق الحالة. */
    protected function setStatus(Exam $exam, string $status): Exam
    {
        $exam->update(['result_status' => $status]);
        $exam->rosterEntries()->delete();

        if (! $exam->fresh()->isEditable()) {
            foreach (Grade::where('exam_id', $exam->id)->pluck('enrollment_id') as $id) {
                ExamRosterEntry::create(['exam_id' => $exam->id, 'enrollment_id' => $id]);
            }
        }

        return $exam->fresh();
    }

    protected function absent(Exam $exam, Enrollment $enrollment): Grade
    {
        $grade = Grade::create(['exam_id' => $exam->id, 'enrollment_id' => $enrollment->id, 'score' => null, 'status' => Grade::STATUS_ABSENT_UNRESOLVED]);

        if (! $exam->fresh()->isEditable()) {
            ExamRosterEntry::firstOrCreate(['exam_id' => $exam->id, 'enrollment_id' => $enrollment->id]);
        }

        return $grade;
    }

    protected function validExamPayload(array $override = []): array
    {
        return $override + [
            'section_id' => $this->section->id, 'name' => 'نصفي', 'type' => 'midterm', 'exam_date' => '2026-11-10',
            'start_time' => '09:00', 'duration_minutes' => 90, 'max_score' => 50, 'weight' => 30,
        ];
    }
}
