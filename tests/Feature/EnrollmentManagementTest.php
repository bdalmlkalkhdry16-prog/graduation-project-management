<?php

namespace Tests\Feature;

use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\AttendanceSession;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\FacultyProfile;
use App\Models\Level;
use App\Models\Program;
use App\Models\Role;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\Section;
use App\Models\Specialization;
use App\Models\StudentProfile;
use App\Models\StudyPlan;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 6.1 — Enrollment Management.
 */
class EnrollmentManagementTest extends TestCase
{
    use RefreshDatabase;

    private Specialization $spec;
    private Program $program;
    private Level $level1;
    private Level $level2;
    private AcademicTerm $term;
    private Course $course;
    private FacultyProfile $faculty;
    private User $facultyUser;
    private Section $section;
    private int $seq = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->spec = Specialization::factory()->create();
        $this->program = Program::create(['specialization_id' => $this->spec->id, 'level' => 'bachelor']);
        $this->level1 = Level::create(['program_id' => $this->program->id, 'level_number' => 1, 'name' => 'المستوى الأول']);
        $this->level2 = Level::create(['program_id' => $this->program->id, 'level_number' => 2, 'name' => 'المستوى الثاني']);
        $year = AcademicYear::create(['name' => '2025-2026', 'year' => 2026]);
        $this->term = AcademicTerm::create(['academic_year_id' => $year->id, 'semester' => 'first']);

        // المقرر ضمن خطة المستوى الأول فقط
        $this->course = Course::create(['specialization_id' => $this->spec->id, 'code' => 'C1', 'name_ar' => 'برمجة 1', 'credit_hours' => 3]);
        StudyPlan::create(['level_id' => $this->level1->id, 'course_id' => $this->course->id]);

        $this->facultyUser = $this->userWithRole('faculty', 'supervisor');
        $this->faculty = FacultyProfile::create(['user_id' => $this->facultyUser->id]);
        $this->section = $this->makeSection();
    }

    private function userWithRole(string $slug, string $state = 'student'): User
    {
        $user = User::factory()->{$state}()->create();
        $user->newRoles()->attach(Role::where('slug', $slug)->first());

        return $user;
    }

    /** مسؤول شؤون الطلاب (الدور المستقل student_affairs). */
    private function staff(): User
    {
        return $this->userWithRole('student_affairs', 'supervisor');
    }

    /** موظف عادي بدور staff العام. */
    private function ordinaryStaff(): User
    {
        return $this->userWithRole('staff', 'supervisor');
    }

    private function makeSection(?Course $course = null, int $capacity = 30, ?FacultyProfile $faculty = null): Section
    {
        return Section::create([
            'course_id' => ($course ?? $this->course)->id,
            'academic_term_id' => $this->term->id,
            'faculty_profile_id' => ($faculty ?? $this->faculty)->id,
            'code' => 'S'.++$this->seq,
            'capacity' => $capacity,
        ]);
    }

    private function makeStudent(?Level $level = null, string $name = null): array
    {
        $user = User::factory()->student()->create($name ? ['name' => $name] : []);
        $user->newRoles()->attach(Role::where('slug', 'student')->first());
        $profile = StudentProfile::create([
            'user_id' => $user->id,
            'number_student' => 'N'.uniqid(),
            'specialization_id' => $this->spec->id,
            'program_id' => $this->program->id,
            'current_level_id' => ($level ?? $this->level1)->id,
        ]);

        return [$user, $profile];
    }

    private function makeSchedule(Section $section): Schedule
    {
        $room = Room::create(['name' => 'R'.uniqid(), 'capacity' => 40]);

        return Schedule::create(['section_id' => $section->id, 'room_id' => $room->id, 'day_of_week' => 'sun', 'start_time' => '08:00', 'end_time' => '09:00']);
    }

    private function enrollUrl(?Section $section = null): string
    {
        return route('staff.enrollments.store', $section ?? $this->section);
    }

    // ---------------------------------------------------------------- الصلاحيات

    /** @test */
    public function student_affairs_can_view_enroll_and_manage_enrollments()
    {
        $staff = $this->staff();
        [, $student] = $this->makeStudent();

        $this->actingAs($staff)->get(route('staff.enrollments.index'))->assertOk()->assertSee('برمجة 1');
        $this->actingAs($staff)->get(route('staff.enrollments.show', $this->section))->assertOk();

        $this->actingAs($staff)->post($this->enrollUrl(), ['student_profile_id' => $student->id])
            ->assertRedirect(route('staff.enrollments.show', $this->section))->assertSessionHasNoErrors();

        $enrollment = Enrollment::first();
        $this->assertSame('active', $enrollment->status);
        $this->assertSame($student->id, $enrollment->student_profile_id);
    }

    /** @test */
    public function a_student_cannot_create_or_manage_enrollments()
    {
        [$studentUser, $student] = $this->makeStudent();
        $enrollment = Enrollment::create(['student_profile_id' => $student->id, 'section_id' => $this->section->id]);

        $this->actingAs($studentUser)->post($this->enrollUrl(), ['student_profile_id' => $student->id])->assertForbidden();
        $this->actingAs($studentUser)->patch(route('staff.enrollments.status', $enrollment), ['status' => 'dropped'])->assertForbidden();
        $this->actingAs($studentUser)->get(route('staff.enrollments.index'))->assertForbidden();
        $this->assertSame('active', $enrollment->fresh()->status);
        $this->assertSame(1, Enrollment::count());
    }

    /** @test */
    public function a_faculty_member_cannot_create_or_manage_enrollments()
    {
        [, $student] = $this->makeStudent();
        $enrollment = Enrollment::create(['student_profile_id' => $student->id, 'section_id' => $this->section->id]);

        $this->actingAs($this->facultyUser)->post($this->enrollUrl(), ['student_profile_id' => $student->id])->assertForbidden();
        $this->actingAs($this->facultyUser)->patch(route('staff.enrollments.status', $enrollment), ['status' => 'dropped'])->assertForbidden();
        $this->actingAs($this->facultyUser)->get(route('staff.enrollments.show', $this->section))->assertForbidden();
        $this->assertSame('active', $enrollment->fresh()->status);
    }

    /** @test */
    public function the_system_admin_cannot_manage_enrollments()
    {
        $admin = $this->userWithRole('admin', 'admin');
        [, $student] = $this->makeStudent();
        $enrollment = Enrollment::create(['student_profile_id' => $student->id, 'section_id' => $this->section->id]);
        [, $other] = $this->makeStudent();

        $this->actingAs($admin)->get(route('staff.enrollments.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('staff.enrollments.show', $this->section))->assertForbidden();
        $this->actingAs($admin)->get(route('staff.enrollments.students'))->assertForbidden();
        $this->actingAs($admin)->get(route('staff.enrollments.history', $student))->assertForbidden();
        $this->actingAs($admin)->post($this->enrollUrl(), ['student_profile_id' => $other->id])->assertForbidden();
        $this->actingAs($admin)->patch(route('staff.enrollments.status', $enrollment), ['status' => 'dropped'])->assertForbidden();

        $this->assertSame(1, Enrollment::count());
        $this->assertSame('active', $enrollment->fresh()->status);
    }

    /** @test */
    public function guests_are_redirected_to_login_from_all_management_urls()
    {
        $this->get(route('staff.enrollments.index'))->assertRedirect(route('login'));
        $this->post($this->enrollUrl(), [])->assertRedirect(route('login'));
        $this->get(route('student.enrollments.index'))->assertRedirect(route('login'));
        $this->get(route('faculty.enrollments.index'))->assertRedirect(route('login'));
    }

    /** @test */
    public function an_ordinary_staff_employee_cannot_manage_enrollments()
    {
        $employee = $this->ordinaryStaff();
        [, $student] = $this->makeStudent();
        $enrollment = Enrollment::create(['student_profile_id' => $student->id, 'section_id' => $this->section->id]);
        [, $other] = $this->makeStudent();

        $this->actingAs($employee)->get(route('staff.enrollments.index'))->assertForbidden();
        $this->actingAs($employee)->get(route('staff.enrollments.show', $this->section))->assertForbidden();
        $this->actingAs($employee)->get(route('staff.enrollments.students'))->assertForbidden();
        $this->actingAs($employee)->get(route('staff.enrollments.history', $student))->assertForbidden();
        $this->actingAs($employee)->post($this->enrollUrl(), ['student_profile_id' => $other->id])->assertForbidden();
        $this->actingAs($employee)->patch(route('staff.enrollments.status', $enrollment), ['status' => 'dropped'])->assertForbidden();

        $this->assertSame(1, Enrollment::count());
        $this->assertSame('active', $enrollment->fresh()->status);
    }

    /** @test */
    public function a_student_affairs_role_holder_can_manage_enrollments()
    {
        $affairs = $this->userWithRole('student_affairs', 'supervisor');
        [, $student] = $this->makeStudent();

        $this->actingAs($affairs)->get(route('staff.enrollments.index'))->assertOk();
        $this->actingAs($affairs)->post($this->enrollUrl(), ['student_profile_id' => $student->id])->assertSessionHasNoErrors();
        $enrollment = Enrollment::firstOrFail();
        $this->actingAs($affairs)->patch(route('staff.enrollments.status', $enrollment), ['status' => 'dropped'])->assertSessionHasNoErrors();
        $this->assertSame('dropped', $enrollment->fresh()->status);
    }

    /** @test */
    public function only_the_student_affairs_role_holds_the_management_permission()
    {
        foreach (['admin', 'faculty', 'student', 'staff', 'supervisor'] as $slug) {
            $this->assertFalse(
                Role::where('slug', $slug)->first()->permissions()->where('slug', 'enrollments.manage')->exists(),
                "الدور {$slug} يجب ألا يملك enrollments.manage"
            );
        }

        $role = Role::where('slug', 'student_affairs')->first();
        $this->assertSame(['enrollments.manage'], $role->permissions()->pluck('slug')->all());
        $this->assertSame(1, \DB::table('role_permissions')
            ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
            ->where('permissions.slug', 'enrollments.manage')->count());
    }

    /** @test */
    public function the_student_affairs_dashboard_shows_the_enrollment_card()
    {
        $affairs = $this->staff();

        $this->actingAs($affairs)->get('/dashboard')->assertOk()
            ->assertSee(route('staff.enrollments.index'), false);
    }

    // ---------------------------------------------------------------- القواعد

    /** @test */
    public function duplicate_enrollment_in_the_same_section_is_prevented_even_if_dropped()
    {
        $staff = $this->staff();
        [, $student] = $this->makeStudent();

        $this->actingAs($staff)->post($this->enrollUrl(), ['student_profile_id' => $student->id])->assertSessionHasNoErrors();
        $this->actingAs($staff)->post($this->enrollUrl(), ['student_profile_id' => $student->id])->assertSessionHasErrors('student_profile_id');
        $this->assertSame(1, Enrollment::count());

        Enrollment::first()->update(['status' => 'dropped']);
        $this->actingAs($staff)->post($this->enrollUrl(), ['student_profile_id' => $student->id])->assertSessionHasErrors('student_profile_id');
        $this->assertSame(1, Enrollment::count());
    }

    /** @test */
    public function an_academically_incompatible_enrollment_is_rejected()
    {
        $staff = $this->staff();

        // مستوى ثانٍ والمقرر ليس في خطته
        [, $wrongLevel] = $this->makeStudent($this->level2);
        // برنامج آخر
        $otherSpec = Specialization::factory()->create();
        $otherProgram = Program::create(['specialization_id' => $otherSpec->id, 'level' => 'bachelor']);
        $otherLevel = Level::create(['program_id' => $otherProgram->id, 'level_number' => 1, 'name' => 'م1']);
        $otherUser = User::factory()->student()->create();
        $otherProgramStudent = StudentProfile::create([
            'user_id' => $otherUser->id, 'number_student' => 'X'.uniqid(),
            'specialization_id' => $otherSpec->id, 'program_id' => $otherProgram->id, 'current_level_id' => $otherLevel->id,
        ]);
        // بلا برنامج ولا مستوى
        $bareUser = User::factory()->student()->create();
        $bare = StudentProfile::create(['user_id' => $bareUser->id, 'number_student' => 'B'.uniqid()]);

        foreach ([$wrongLevel, $otherProgramStudent, $bare] as $student) {
            $this->actingAs($staff)->post($this->enrollUrl(), ['student_profile_id' => $student->id])
                ->assertSessionHasErrors('student_profile_id');
        }

        $this->assertSame(0, Enrollment::count());
    }

    /** @test */
    public function non_existent_students_and_sections_are_rejected()
    {
        $staff = $this->staff();

        $this->actingAs($staff)->post($this->enrollUrl(), ['student_profile_id' => 999999])->assertSessionHasErrors('student_profile_id');
        $this->actingAs($staff)->post(route('staff.enrollments.store', 999999), ['student_profile_id' => 1])->assertNotFound();
        $this->actingAs($staff)->patch(route('staff.enrollments.status', 999999), ['status' => 'dropped'])->assertNotFound();
        $this->assertSame(0, Enrollment::count());
    }

    /** @test */
    public function section_capacity_is_not_enforced_because_it_is_not_an_approved_rule()
    {
        $staff = $this->staff();
        $section = $this->makeSection(null, 1);
        [, $first] = $this->makeStudent();
        [, $second] = $this->makeStudent();

        $this->actingAs($staff)->post($this->enrollUrl($section), ['student_profile_id' => $first->id])->assertSessionHasNoErrors();
        $this->actingAs($staff)->post($this->enrollUrl($section), ['student_profile_id' => $second->id])->assertSessionHasNoErrors();
        $this->assertSame(2, $section->enrollments()->active()->count());

        $one = Enrollment::where('student_profile_id', $first->id)->first();
        $this->actingAs($staff)->patch(route('staff.enrollments.status', $one), ['status' => 'dropped'])->assertSessionHasNoErrors();
        $this->actingAs($staff)->patch(route('staff.enrollments.status', $one), ['status' => 'active'])->assertSessionHasNoErrors();
        $this->assertSame('active', $one->fresh()->status);
    }

    /** @test */
    public function status_changes_never_delete_the_enrollment()
    {
        $staff = $this->staff();
        [, $student] = $this->makeStudent();
        $enrollment = Enrollment::create(['student_profile_id' => $student->id, 'section_id' => $this->section->id]);

        foreach (['dropped', 'completed', 'active'] as $status) {
            $this->actingAs($staff)->patch(route('staff.enrollments.status', $enrollment), ['status' => $status])->assertSessionHasNoErrors();
            $this->assertSame($status, $enrollment->fresh()->status);
            $this->assertSame(1, Enrollment::count());
        }

        $this->actingAs($staff)->patch(route('staff.enrollments.status', $enrollment), ['status' => 'deleted'])->assertSessionHasErrors('status');
        $this->assertSame('active', $enrollment->fresh()->status);
    }

    // ---------------------------------------------------------------- العرض والبحث

    /** @test */
    public function the_section_page_lists_enrolled_and_only_eligible_not_yet_enrolled_students()
    {
        $staff = $this->staff();
        [, $enrolled] = $this->makeStudent(null, 'طالب مسجل');
        [, $eligible] = $this->makeStudent(null, 'طالب مؤهل');
        [, $wrongLevel] = $this->makeStudent($this->level2, 'طالب مستوى ثان');
        Enrollment::create(['student_profile_id' => $enrolled->id, 'section_id' => $this->section->id]);

        $response = $this->actingAs($staff)->get(route('staff.enrollments.show', $this->section))->assertOk();

        $this->assertSame([$enrolled->id], $response->viewData('enrollments')->pluck('student_profile_id')->all());
        $this->assertSame([$eligible->id], $response->viewData('eligible')->pluck('id')->all());
    }

    /** @test */
    public function students_can_be_searched_by_name_or_academic_number()
    {
        $staff = $this->staff();
        [, $ahmed] = $this->makeStudent(null, 'أحمد الفريد');
        [, $sara] = $this->makeStudent(null, 'سارة منصور');

        $byName = $this->actingAs($staff)->get(route('staff.enrollments.show', ['section' => $this->section, 'q' => 'الفريد']));
        $this->assertSame([$ahmed->id], $byName->viewData('eligible')->pluck('id')->all());

        $byNumber = $this->actingAs($staff)->get(route('staff.enrollments.students', ['q' => $sara->number_student]));
        $this->assertSame([$sara->id], $byNumber->viewData('students')->pluck('id')->all());
    }

    /** @test */
    public function the_sections_page_filters_by_academic_term()
    {
        $staff = $this->staff();
        $otherYear = AcademicYear::create(['name' => '2026-2027', 'year' => 2027]);
        $otherTerm = AcademicTerm::create(['academic_year_id' => $otherYear->id, 'semester' => 'first']);
        $otherSection = Section::create(['course_id' => $this->course->id, 'academic_term_id' => $otherTerm->id, 'faculty_profile_id' => $this->faculty->id, 'code' => 'Z', 'capacity' => 10]);

        $r = $this->actingAs($staff)->get(route('staff.enrollments.index', ['term' => $otherTerm->id]))->assertOk();

        $this->assertSame([$otherSection->id], $r->viewData('sections')->pluck('id')->all());
    }

    /** @test */
    public function the_student_history_lists_all_their_enrollments_with_statuses()
    {
        $staff = $this->staff();
        [, $student] = $this->makeStudent();
        $second = $this->makeSection();
        Enrollment::create(['student_profile_id' => $student->id, 'section_id' => $this->section->id, 'status' => 'completed']);
        Enrollment::create(['student_profile_id' => $student->id, 'section_id' => $second->id, 'status' => 'dropped']);

        $r = $this->actingAs($staff)->get(route('staff.enrollments.history', $student))->assertOk();

        $this->assertCount(2, $r->viewData('enrollments'));
        $r->assertSee('منسحب')->assertSee('مكتمل');
    }

    // ---------------------------------------------------------------- الطالب والمدرس

    /** @test */
    public function a_student_sees_only_their_active_enrollments()
    {
        [$studentUser, $student] = $this->makeStudent();
        $dropped = $this->makeSection();
        $completed = $this->makeSection();
        Enrollment::create(['student_profile_id' => $student->id, 'section_id' => $this->section->id]);
        Enrollment::create(['student_profile_id' => $student->id, 'section_id' => $dropped->id, 'status' => 'dropped']);
        Enrollment::create(['student_profile_id' => $student->id, 'section_id' => $completed->id, 'status' => 'completed']);

        $r = $this->actingAs($studentUser)->get(route('student.enrollments.index'))->assertOk();

        $this->assertSame([$this->section->id], $r->viewData('enrollments')->pluck('section_id')->all());
    }

    /** @test */
    public function a_faculty_member_sees_only_active_students_of_their_own_sections()
    {
        [, $active] = $this->makeStudent(null, 'طالب نشط');
        [, $dropped] = $this->makeStudent(null, 'طالب منسحب');
        Enrollment::create(['student_profile_id' => $active->id, 'section_id' => $this->section->id]);
        Enrollment::create(['student_profile_id' => $dropped->id, 'section_id' => $this->section->id, 'status' => 'dropped']);

        $r = $this->actingAs($this->facultyUser)->get(route('faculty.enrollments.show', $this->section))->assertOk();
        $r->assertSee('طالب نشط')->assertDontSee('طالب منسحب');

        // شعبة مدرس آخر → ممنوع
        $otherUser = $this->userWithRole('faculty', 'supervisor');
        $otherFaculty = FacultyProfile::create(['user_id' => $otherUser->id]);
        $foreign = $this->makeSection(null, 30, $otherFaculty);
        $this->actingAs($this->facultyUser)->get(route('faculty.enrollments.show', $foreign))->assertForbidden();

        $this->actingAs($this->facultyUser)->get(route('faculty.enrollments.index'))->assertOk();
    }

    /** @test */
    public function students_cannot_open_faculty_roster_pages_and_vice_versa()
    {
        [$studentUser] = $this->makeStudent();

        $this->actingAs($studentUser)->get(route('faculty.enrollments.show', $this->section))->assertForbidden();
        $this->actingAs($this->facultyUser)->get(route('student.enrollments.index'))->assertForbidden();
    }

    /** @test */
    public function dropping_via_management_removes_the_student_from_schedule_and_attendance()
    {
        $staff = $this->staff();
        [$studentUser, $student] = $this->makeStudent(null, 'طالب سيُسحب');
        $schedule = $this->makeSchedule($this->section);
        $this->actingAs($staff)->post($this->enrollUrl(), ['student_profile_id' => $student->id]);
        $enrollment = Enrollment::first();
        $session = AttendanceSession::create(['schedule_id' => $schedule->id, 'session_date' => '2026-10-05']);

        $this->assertCount(1, $this->actingAs($studentUser)->get(route('student.schedule.index'))->viewData('schedules'));
        $this->actingAs($this->facultyUser)->get(route('faculty.attendance.show', $session))->assertSee('طالب سيُسحب');

        $this->actingAs($staff)->patch(route('staff.enrollments.status', $enrollment), ['status' => 'dropped']);

        $this->assertCount(0, $this->actingAs($studentUser)->get(route('student.schedule.index'))->viewData('schedules'));
        $this->actingAs($this->facultyUser)->get(route('faculty.attendance.show', $session))->assertDontSee('طالب سيُسحب');
    }
}
