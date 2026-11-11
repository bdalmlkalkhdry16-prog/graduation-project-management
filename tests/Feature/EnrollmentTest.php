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
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentTest extends TestCase
{
    use RefreshDatabase;

    private Specialization $spec;
    private Program $program;
    private Level $level;
    private AcademicTerm $term;
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
    }

    private function makeCourse(): Course
    {
        $course = Course::create([
            'specialization_id' => $this->spec->id,
            'code' => 'C'.uniqid(),
            'name_ar' => 'مقرر',
            'credit_hours' => 3,
        ]);
        StudyPlan::create(['program_id' => $this->program->id, 'level_id' => $this->level->id, 'course_id' => $course->id]);

        return $course;
    }

    private function makeFaculty(): array
    {
        $user = User::factory()->supervisor()->create();
        $user->newRoles()->attach(Role::where('slug', 'faculty')->first());

        return [$user, FacultyProfile::create(['user_id' => $user->id])];
    }

    private function makeSection(FacultyProfile $faculty, ?Course $course = null): Section
    {
        return Section::create([
            'course_id' => ($course ?? $this->makeCourse())->id,
            'academic_term_id' => $this->term->id,
            'faculty_profile_id' => $faculty->id,
            'code' => 'S'.++$this->seq,
            'capacity' => 30,
        ]);
    }

    private function makeStudent(): array
    {
        $user = User::factory()->student()->create();
        $user->newRoles()->attach(Role::where('slug', 'student')->first());
        $profile = StudentProfile::create([
            'user_id' => $user->id,
            'number_student' => 'N'.uniqid(),
            'specialization_id' => $this->spec->id,
            'program_id' => $this->program->id,
            'current_level_id' => $this->level->id,
        ]);

        return [$user, $profile];
    }

    private function makeSchedule(Section $section, string $day = 'sun'): Schedule
    {
        $room = Room::create(['name' => 'R'.uniqid(), 'capacity' => 40]);

        return Schedule::create([
            'section_id' => $section->id,
            'room_id' => $room->id,
            'day_of_week' => $day,
            'start_time' => '08:00',
            'end_time' => '09:00',
        ]);
    }

    /** @test */
    public function the_same_student_cannot_be_enrolled_twice_in_the_same_section()
    {
        [, $faculty] = $this->makeFaculty();
        $section = $this->makeSection($faculty);
        [, $student] = $this->makeStudent();

        Enrollment::create(['student_profile_id' => $student->id, 'section_id' => $section->id]);

        $this->expectException(QueryException::class);
        Enrollment::create(['student_profile_id' => $student->id, 'section_id' => $section->id]);
    }

    /** @test */
    public function the_same_student_can_be_enrolled_in_different_sections()
    {
        [, $faculty] = $this->makeFaculty();
        [, $student] = $this->makeStudent();
        $a = $this->makeSection($faculty);
        $b = $this->makeSection($faculty);

        $student->sections()->attach([$a->id, $b->id]);

        $this->assertCount(2, $student->fresh()->sections);
        $this->assertSame(2, $student->enrollments()->count());
    }

    /** @test */
    public function relations_work_in_both_directions()
    {
        [, $faculty] = $this->makeFaculty();
        $section = $this->makeSection($faculty);
        [, $student] = $this->makeStudent();

        $enrollment = Enrollment::create(['student_profile_id' => $student->id, 'section_id' => $section->id]);

        $this->assertTrue($section->studentProfiles->first()->is($student));
        $this->assertTrue($student->sections->first()->is($section));
        $this->assertTrue($enrollment->studentProfile->is($student));
        $this->assertTrue($enrollment->section->is($section));
        $this->assertSame(1, $section->enrollments()->count());
    }

    /** @test */
    public function deleting_a_section_removes_its_enrollments()
    {
        [, $faculty] = $this->makeFaculty();
        $section = $this->makeSection($faculty);
        [, $student] = $this->makeStudent();
        $student->sections()->attach($section->id);

        $section->delete();

        $this->assertSame(0, Enrollment::count());
    }

    /** @test */
    public function the_attendance_roster_contains_only_enrolled_students()
    {
        [$facultyUser, $faculty] = $this->makeFaculty();
        $section = $this->makeSection($faculty);
        $schedule = $this->makeSchedule($section);
        [$enrolledUser, $enrolled] = $this->makeStudent();
        [$otherUser] = $this->makeStudent(); // نفس المستوى لكن غير مسجَّل
        $enrolled->sections()->attach($section->id);

        $session = AttendanceSession::create(['schedule_id' => $schedule->id, 'session_date' => '2026-10-01']);

        $response = $this->actingAs($facultyUser)->get(route('faculty.attendance.show', $session));

        $response->assertOk();
        $response->assertSee($enrolledUser->name);
        $response->assertDontSee($otherUser->name);
        $this->assertCount(1, $response->viewData('roster'));
    }

    /** @test */
    public function the_student_schedule_shows_only_enrolled_sections()
    {
        [, $faculty] = $this->makeFaculty();
        $mine = $this->makeSection($faculty, $this->makeCourse());
        $notMine = $this->makeSection($faculty, $this->makeCourse());
        $this->makeSchedule($mine, 'mon');
        $this->makeSchedule($notMine, 'tue');
        [$studentUser, $student] = $this->makeStudent();
        $student->sections()->attach($mine->id);

        $response = $this->actingAs($studentUser)->get(route('student.schedule.index'));

        $response->assertOk();
        $schedules = $response->viewData('schedules');
        $this->assertCount(1, $schedules);
        $this->assertSame($mine->id, $schedules->first()->section_id);
    }

    /** @test */
    public function a_student_without_a_current_level_still_sees_enrolled_schedules()
    {
        [, $faculty] = $this->makeFaculty();
        $section = $this->makeSection($faculty);
        $this->makeSchedule($section);
        [$studentUser, $student] = $this->makeStudent();
        $student->sections()->attach($section->id);
        $student->update(['current_level_id' => null]);

        $response = $this->actingAs($studentUser)->get(route('student.schedule.index'));

        $response->assertOk();
        $this->assertCount(1, $response->viewData('schedules'));
    }

    /** @test */
    public function the_faculty_role_holds_schedules_view_own_and_can_open_its_schedule()
    {
        $faculty = Role::where('slug', 'faculty')->first();
        $this->assertTrue($faculty->permissions()->where('slug', 'schedules.viewOwn')->exists());

        [$facultyUser] = $this->makeFaculty();

        $this->actingAs($facultyUser)->get(route('faculty.schedule.index'))->assertOk();
    }

    /** @test */
    public function an_enrollment_defaults_to_active_with_an_enrolled_date()
    {
        [, $faculty] = $this->makeFaculty();
        $section = $this->makeSection($faculty);
        [, $student] = $this->makeStudent();

        $enrollment = Enrollment::create(['student_profile_id' => $student->id, 'section_id' => $section->id])->fresh();

        $this->assertSame(Enrollment::STATUS_ACTIVE, $enrollment->status);
        $this->assertNotNull($enrollment->enrolled_at);
        $this->assertTrue($enrollment->enrolled_at->isToday());
    }

    /** @test */
    public function all_three_statuses_are_supported()
    {
        [, $faculty] = $this->makeFaculty();
        $section = $this->makeSection($faculty);

        foreach (Enrollment::STATUSES as $status) {
            [, $student] = $this->makeStudent();
            $e = Enrollment::create(['student_profile_id' => $student->id, 'section_id' => $section->id, 'status' => $status]);
            $this->assertSame($status, $e->fresh()->status);
        }

        $this->assertSame(['active', 'dropped', 'completed'], Enrollment::STATUSES);
        $this->assertSame(1, Enrollment::active()->count());
    }

    /** @test */
    public function an_unknown_status_is_rejected_by_the_database()
    {
        [, $faculty] = $this->makeFaculty();
        $section = $this->makeSection($faculty);
        [, $student] = $this->makeStudent();

        $this->expectException(QueryException::class);
        Enrollment::create(['student_profile_id' => $student->id, 'section_id' => $section->id, 'status' => 'bogus']);
    }

    /** @test */
    public function non_existent_foreign_keys_are_rejected()
    {
        [, $faculty] = $this->makeFaculty();
        $section = $this->makeSection($faculty);
        [, $student] = $this->makeStudent();
        \DB::statement('PRAGMA foreign_keys = ON');

        try {
            Enrollment::create(['student_profile_id' => 999999, 'section_id' => $section->id]);
            $this->fail('student_profile_id غير موجود كان يجب أن يُرفض');
        } catch (QueryException $e) {
            $this->assertStringContainsString('FOREIGN KEY', $e->getMessage());
        }

        try {
            Enrollment::create(['student_profile_id' => $student->id, 'section_id' => 999999]);
            $this->fail('section_id غير موجود كان يجب أن يُرفض');
        } catch (QueryException $e) {
            $this->assertStringContainsString('FOREIGN KEY', $e->getMessage());
        }
    }

    /** @test */
    public function dropped_and_completed_enrollments_do_not_appear_in_schedule_or_attendance()
    {
        [$facultyUser, $faculty] = $this->makeFaculty();
        $section = $this->makeSection($faculty);
        $schedule = $this->makeSchedule($section);
        [$activeUser, $active] = $this->makeStudent();
        [$droppedUser, $dropped] = $this->makeStudent();
        [$completedUser, $completed] = $this->makeStudent();
        Enrollment::create(['student_profile_id' => $active->id, 'section_id' => $section->id]);
        Enrollment::create(['student_profile_id' => $dropped->id, 'section_id' => $section->id, 'status' => 'dropped']);
        Enrollment::create(['student_profile_id' => $completed->id, 'section_id' => $section->id, 'status' => 'completed']);

        // الحضور
        $session = AttendanceSession::create(['schedule_id' => $schedule->id, 'session_date' => '2026-10-02']);
        $response = $this->actingAs($facultyUser)->get(route('faculty.attendance.show', $session));
        $response->assertOk()->assertSee($activeUser->name)
            ->assertDontSee($droppedUser->name)->assertDontSee($completedUser->name);
        $this->assertCount(1, $response->viewData('roster'));

        // الجدول
        $this->assertCount(1, $this->actingAs($activeUser)->get(route('student.schedule.index'))->viewData('schedules'));
        $this->assertCount(0, $this->actingAs($droppedUser)->get(route('student.schedule.index'))->viewData('schedules'));
        $this->assertCount(0, $this->actingAs($completedUser)->get(route('student.schedule.index'))->viewData('schedules'));
    }

    /** @test */
    public function attendance_cannot_be_submitted_for_a_student_not_enrolled_in_the_section()
    {
        [$facultyUser, $faculty] = $this->makeFaculty();
        $section = $this->makeSection($faculty);
        $schedule = $this->makeSchedule($section);
        [, $enrolled] = $this->makeStudent();
        [, $outsider] = $this->makeStudent();           // student_profile_id صحيح لكن غير مسجَّل
        [, $dropped] = $this->makeStudent();
        Enrollment::create(['student_profile_id' => $enrolled->id, 'section_id' => $section->id]);
        Enrollment::create(['student_profile_id' => $dropped->id, 'section_id' => $section->id, 'status' => 'dropped']);
        // مسجَّل في شعبة أخرى فقط
        $other = $this->makeSection($faculty);
        Enrollment::create(['student_profile_id' => $outsider->id, 'section_id' => $other->id]);

        $session = AttendanceSession::create(['schedule_id' => $schedule->id, 'session_date' => '2026-10-03']);
        $url = route('faculty.attendance.update', $session);

        foreach ([$outsider, $dropped] as $bad) {
            $this->actingAs($facultyUser)
                ->put($url, ['records' => [['student_profile_id' => $bad->id, 'status' => 'present']]])
                ->assertSessionHasErrors('records.0.student_profile_id');
        }
        $this->assertSame(0, \App\Models\AttendanceRecord::count());

        // المسجَّل فعليًا يُقبل
        $this->actingAs($facultyUser)
            ->put($url, ['records' => [['student_profile_id' => $enrolled->id, 'status' => 'present']]])
            ->assertSessionHasNoErrors();
        $this->assertSame(1, \App\Models\AttendanceRecord::count());
    }
}
