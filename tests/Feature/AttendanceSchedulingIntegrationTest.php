<?php

namespace Tests\Feature;

use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\AttendanceRecord;
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
 * Phase 6.1 — تكامل Enrollment مع الحضور والجداول والصلاحيات.
 */
class AttendanceSchedulingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private Specialization $spec;
    private Program $program;
    private Level $level;
    private AcademicTerm $term;
    private Course $course;
    private User $facultyUser;
    private FacultyProfile $faculty;
    private Section $section;
    private Schedule $schedule;
    private int $seq = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->spec = Specialization::factory()->create();
        $this->program = Program::create(['specialization_id' => $this->spec->id, 'level' => 'bachelor']);
        $this->level = Level::create(['program_id' => $this->program->id, 'level_number' => 1, 'name' => 'المستوى الأول']);
        $year = AcademicYear::create(['name' => '2025-2026', 'year' => 2026]);
        $this->term = AcademicTerm::create(['academic_year_id' => $year->id, 'semester' => 'first']);
        $this->course = Course::create(['specialization_id' => $this->spec->id, 'code' => 'C1', 'name_ar' => 'برمجة 1', 'credit_hours' => 3]);
        StudyPlan::create(['level_id' => $this->level->id, 'course_id' => $this->course->id]);

        [$this->facultyUser, $this->faculty] = $this->makeFaculty();
        $this->section = $this->makeSection($this->faculty);
        $this->schedule = $this->makeSchedule($this->section);
    }

    private function userWithRole(string $slug, string $state): User
    {
        $user = User::factory()->{$state}()->create();
        $user->newRoles()->attach(Role::where('slug', $slug)->first());

        return $user;
    }

    private function makeFaculty(): array
    {
        $user = $this->userWithRole('faculty', 'supervisor');

        return [$user, FacultyProfile::create(['user_id' => $user->id])];
    }

    private function makeSection(FacultyProfile $faculty): Section
    {
        return Section::create([
            'course_id' => $this->course->id,
            'academic_term_id' => $this->term->id,
            'faculty_profile_id' => $faculty->id,
            'code' => 'S'.++$this->seq,
            'capacity' => 30,
        ]);
    }

    private function makeSchedule(Section $section, string $day = 'sun'): Schedule
    {
        $room = Room::create(['name' => 'R'.uniqid(), 'capacity' => 40]);

        return Schedule::create(['section_id' => $section->id, 'room_id' => $room->id, 'day_of_week' => $day, 'start_time' => '08:00', 'end_time' => '09:00']);
    }

    private function makeStudent(string $name = null): array
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

    private function enroll(StudentProfile $student, ?Section $section = null, string $status = 'active'): Enrollment
    {
        return Enrollment::create(['student_profile_id' => $student->id, 'section_id' => ($section ?? $this->section)->id, 'status' => $status]);
    }

    private function makeSession(?Schedule $schedule = null, string $date = '2026-10-10'): AttendanceSession
    {
        return AttendanceSession::create(['schedule_id' => ($schedule ?? $this->schedule)->id, 'session_date' => $date]);
    }

    // ------------------------------------------------------------ الحضور

    /** @test */
    public function the_attendance_roster_does_not_leak_students_of_other_sections()
    {
        $otherSection = $this->makeSection($this->faculty);
        [, $mine] = $this->makeStudent('طالب شعبتي');
        [, $theirs] = $this->makeStudent('طالب شعبة أخرى');
        $this->enroll($mine);
        $this->enroll($theirs, $otherSection);

        $this->actingAs($this->facultyUser)->get(route('faculty.attendance.show', $this->makeSession()))
            ->assertOk()->assertSee('طالب شعبتي')->assertDontSee('طالب شعبة أخرى');
    }

    /** @test */
    public function a_reactivated_enrollment_returns_to_roster_and_schedule()
    {
        [$studentUser, $student] = $this->makeStudent('طالب يعود');
        $enrollment = $this->enroll($student, null, 'dropped');
        $session = $this->makeSession();

        $this->actingAs($this->facultyUser)->get(route('faculty.attendance.show', $session))->assertDontSee('طالب يعود');
        $this->assertCount(0, $this->actingAs($studentUser)->get(route('student.schedule.index'))->viewData('schedules'));

        $enrollment->update(['status' => 'active']);

        $this->actingAs($this->facultyUser)->get(route('faculty.attendance.show', $session))->assertSee('طالب يعود');
        $this->assertCount(1, $this->actingAs($studentUser)->get(route('student.schedule.index'))->viewData('schedules'));
    }

    /** @test */
    public function a_mixed_attendance_payload_with_a_non_enrolled_student_saves_nothing()
    {
        [, $enrolled] = $this->makeStudent();
        [, $outsider] = $this->makeStudent();
        $this->enroll($enrolled);
        $session = $this->makeSession();

        $this->actingAs($this->facultyUser)->put(route('faculty.attendance.update', $session), ['records' => [
            ['student_profile_id' => $enrolled->id, 'status' => 'present'],
            ['student_profile_id' => $outsider->id, 'status' => 'present'],
        ]])->assertSessionHasErrors('records.1.student_profile_id');

        $this->assertSame(0, AttendanceRecord::count());
    }

    /** @test */
    public function previous_attendance_records_are_preserved_when_a_student_drops()
    {
        [$studentUser, $dropped] = $this->makeStudent('طالب منسحب');
        [, $stays] = $this->makeStudent('طالب باقٍ');
        $droppedEnrollment = $this->enroll($dropped);
        $this->enroll($stays);
        $session = $this->makeSession();
        $url = route('faculty.attendance.update', $session);

        $this->actingAs($this->facultyUser)->put($url, ['records' => [
            ['student_profile_id' => $dropped->id, 'status' => 'excused'],
            ['student_profile_id' => $stays->id, 'status' => 'present'],
        ]])->assertSessionHasNoErrors();

        $droppedEnrollment->update(['status' => 'dropped']);

        // إعادة حفظ الكشف بعد السحب: السجل القديم لا يُحذف ولا يتغيّر
        $this->actingAs($this->facultyUser)->put($url, ['records' => [
            ['student_profile_id' => $stays->id, 'status' => 'late'],
        ]])->assertSessionHasNoErrors();
        $this->assertSame('excused', AttendanceRecord::where('student_profile_id', $dropped->id)->value('status'));
        $this->assertSame(2, AttendanceRecord::count());

        // ولا يمكن تعديله بعد السحب
        $this->actingAs($this->facultyUser)->put($url, ['records' => [
            ['student_profile_id' => $dropped->id, 'status' => 'absent'],
        ]])->assertSessionHasErrors('records.0.student_profile_id');
        $this->assertSame('excused', AttendanceRecord::where('student_profile_id', $dropped->id)->value('status'));

        // الطالب ما زال يرى سجله، والموظف يراه في الإشراف
        $this->actingAs($studentUser)->get(route('student.attendance.index'))->assertOk()->assertSee('معذور');
        $staff = $this->userWithRole('staff', 'supervisor');
        $this->actingAs($staff)->get(route('staff.attendance.show', $session))->assertOk()->assertSee('طالب منسحب');
    }

    /** @test */
    public function a_student_sees_only_their_own_attendance_records()
    {
        [$userA, $a] = $this->makeStudent();
        [, $b] = $this->makeStudent();
        $this->enroll($a);
        $this->enroll($b);
        $session = $this->makeSession();
        AttendanceRecord::create(['attendance_session_id' => $session->id, 'student_profile_id' => $a->id, 'status' => 'absent']);
        AttendanceRecord::create(['attendance_session_id' => $session->id, 'student_profile_id' => $b->id, 'status' => 'present']);

        $records = $this->actingAs($userA)->get(route('student.attendance.index'))->assertOk()->viewData('records');

        $this->assertSame([$a->id], $records->pluck('student_profile_id')->all());
    }

    /** @test */
    public function a_faculty_member_can_only_manage_attendance_of_their_own_sections()
    {
        [$otherUser, $otherFaculty] = $this->makeFaculty();
        $foreignSchedule = $this->makeSchedule($this->makeSection($otherFaculty), 'mon');
        $foreignSession = $this->makeSession($foreignSchedule);
        [, $student] = $this->makeStudent();
        $this->enroll($student, $foreignSchedule->section);

        $this->actingAs($this->facultyUser)->get(route('faculty.attendance.show', $foreignSession))->assertForbidden();
        $this->actingAs($this->facultyUser)->put(route('faculty.attendance.update', $foreignSession), ['records' => [
            ['student_profile_id' => $student->id, 'status' => 'present'],
        ]])->assertForbidden();
        $this->actingAs($this->facultyUser)->post(route('faculty.attendance.store'), [
            'schedule_id' => $foreignSchedule->id, 'session_date' => '2026-11-01',
        ])->assertForbidden();

        $this->assertSame(0, AttendanceRecord::count());
        $this->assertSame(1, AttendanceSession::count());
    }

    /** @test */
    public function only_faculty_can_take_attendance_not_students_ordinary_staff_or_admin()
    {
        $session = $this->makeSession();
        [$studentUser] = $this->makeStudent();
        $staff = $this->userWithRole('staff', 'supervisor');
        $admin = $this->userWithRole('admin', 'admin');

        foreach ([$studentUser, $staff, $admin] as $user) {
            $this->actingAs($user)->get(route('faculty.attendance.index'))->assertForbidden();
            $this->actingAs($user)->get(route('faculty.attendance.show', $session))->assertForbidden();
            $this->actingAs($user)->put(route('faculty.attendance.update', $session), ['records' => []])->assertForbidden();
        }
    }

    // ------------------------------------------------------------ الجداول

    /** @test */
    public function each_student_sees_only_the_schedules_of_their_own_enrollments()
    {
        $otherSection = $this->makeSection($this->faculty);
        $otherSchedule = $this->makeSchedule($otherSection, 'tue');
        [$userA, $a] = $this->makeStudent();
        [$userB, $b] = $this->makeStudent();
        $this->enroll($a);
        $this->enroll($b, $otherSection);

        $this->assertSame([$this->schedule->id], $this->actingAs($userA)->get(route('student.schedule.index'))->viewData('schedules')->pluck('id')->all());
        $this->assertSame([$otherSchedule->id], $this->actingAs($userB)->get(route('student.schedule.index'))->viewData('schedules')->pluck('id')->all());
    }

    /** @test */
    public function a_user_without_a_student_profile_gets_an_empty_schedule_without_error()
    {
        $user = $this->userWithRole('student', 'student');

        $this->actingAs($user)->get(route('student.schedule.index'))->assertOk();
        $this->assertCount(0, $this->actingAs($user)->get(route('student.schedule.index'))->viewData('schedules'));
    }

    /** @test */
    public function schedule_management_is_closed_to_students_and_faculty_and_their_own_views_do_not_leak()
    {
        [$studentUser, $student] = $this->makeStudent();
        $this->enroll($student);
        [$otherUser] = $this->makeFaculty();

        $this->actingAs($studentUser)->get(route('staff.schedules.index'))->assertForbidden();
        $this->actingAs($this->facultyUser)->get(route('staff.schedules.index'))->assertForbidden();

        // طالب يفتح جدول المدرس: لا يظهر له شيء
        $this->assertCount(0, $this->actingAs($studentUser)->get(route('faculty.schedule.index'))->viewData('schedules'));
        // مدرس آخر لا يرى جدول هذه الشعبة
        $this->assertCount(0, $this->actingAs($otherUser)->get(route('faculty.schedule.index'))->viewData('schedules'));
        $this->assertCount(1, $this->actingAs($this->facultyUser)->get(route('faculty.schedule.index'))->viewData('schedules'));
    }

    // ------------------------------------------------------------ Enrollment

    /** @test */
    public function the_enrolled_students_list_in_management_can_be_searched()
    {
        $affairs = $this->userWithRole('student_affairs', 'supervisor');
        [, $ahmed] = $this->makeStudent('أحمد الكعبي');
        [, $sara] = $this->makeStudent('سارة الحداد');
        $this->enroll($ahmed);
        $this->enroll($sara);

        $byName = $this->actingAs($affairs)->get(route('staff.enrollments.show', ['section' => $this->section, 'q' => 'الكعبي']));
        $this->assertSame([$ahmed->id], $byName->viewData('enrollments')->pluck('student_profile_id')->all());

        $byNumber = $this->actingAs($affairs)->get(route('staff.enrollments.show', ['section' => $this->section, 'q' => $sara->number_student]));
        $this->assertSame([$sara->id], $byNumber->viewData('enrollments')->pluck('student_profile_id')->all());
    }
}
