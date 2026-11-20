<?php

namespace Tests\Feature;

use App\Models\StudentServiceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AcademicFixtures;
use Tests\TestCase;

/**
 * E3 — التظلم على درجة مقرر محدد.
 */
class GradeAppealRequestTest extends TestCase
{
    use RefreshDatabase, AcademicFixtures;

    private $studentUser;
    private $profile;
    private $officer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAcademic();
        [$this->studentUser, $this->profile] = $this->makeStudent();
        $this->officer = $this->userWithRole('eservices_officer');
    }

    /** مقرر للطالب بنتيجة بحالة معينة. */
    private function enrolledWithResult(string $status = 'published', $profile = null, string $code = null)
    {
        $course = $code ? $this->makeCourse($code) : $this->course;
        $section = $this->makeSection($this->faculty, $course);
        $enrollment = $this->enroll($profile ?? $this->profile, $section);
        $exam = $this->makeExam($section, ['result_status' => $status, 'published_at' => $status === 'published' ? now() : null]);
        $this->setGrade($exam, $enrollment, 60);

        return $enrollment;
    }

    private function appeal($enrollmentId, string $reason = 'أرى أن الدرجة لا تعكس إجابتي', $user = null)
    {
        return $this->actingAs($user ?? $this->studentUser)->post(route('student.service-requests.store'), [
            'type' => 'grade_appeal', 'enrollment_id' => $enrollmentId, 'description' => $reason,
        ]);
    }

    /** @test */
    public function the_student_can_appeal_a_course_with_a_published_grade()
    {
        $e = $this->enrolledWithResult();

        $this->appeal($e->id)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('student_service_requests', [
            'student_profile_id' => $this->profile->id, 'type' => 'grade_appeal', 'enrollment_id' => $e->id, 'status' => 'pending',
        ]);
    }

    /** @test */
    public function the_appeal_form_lists_only_courses_with_published_grades()
    {
        $this->enrolledWithResult('published', null, 'PUB1');
        $this->enrolledWithResult('submitted', null, 'SUB1');
        $this->enrolledWithResult('draft', null, 'DRF1');

        $this->actingAs($this->studentUser)->get(route('student.service-requests.create'))->assertOk()
            ->assertSee('PUB1')->assertDontSee('SUB1')->assertDontSee('DRF1');
    }

    /** @test */
    public function an_appeal_on_unpublished_results_is_rejected()
    {
        foreach (['draft', 'submitted', 'approved'] as $status) {
            $e = $this->enrolledWithResult($status, null, 'X'.$status);
            $this->appeal($e->id)->assertSessionHasErrors('enrollment_id');
        }
        $this->assertSame(0, StudentServiceRequest::count());
    }

    /** @test */
    public function a_student_cannot_appeal_another_students_course()
    {
        [, $other] = $this->makeStudent();
        $theirs = $this->enrolledWithResult('published', $other);

        $this->appeal($theirs->id)->assertSessionHasErrors('enrollment_id');
        $this->assertSame(0, StudentServiceRequest::count());
    }

    /** @test */
    public function the_course_and_a_reason_are_required_for_an_appeal()
    {
        $e = $this->enrolledWithResult();

        $this->actingAs($this->studentUser)->post(route('student.service-requests.store'), ['type' => 'grade_appeal', 'description' => 'سبب كافٍ ومفصل'])
            ->assertSessionHasErrors('enrollment_id');
        $this->appeal($e->id, '')->assertSessionHasErrors('description');
        $this->appeal($e->id, 'قصير')->assertSessionHasErrors('description');
        $this->assertSame(0, StudentServiceRequest::count());
    }

    /** @test */
    public function only_one_open_appeal_per_course_but_other_courses_are_independent()
    {
        $a = $this->enrolledWithResult('published', null, 'A1');
        $b = $this->enrolledWithResult('published', null, 'B1');

        $this->appeal($a->id)->assertSessionHasNoErrors();
        $this->appeal($a->id)->assertSessionHasErrors('type');
        $this->appeal($b->id)->assertSessionHasNoErrors();
        $this->assertSame(2, StudentServiceRequest::count());

        StudentServiceRequest::where('enrollment_id', $a->id)->update(['status' => 'rejected']);
        $this->appeal($a->id)->assertSessionHasNoErrors();
    }

    /** @test */
    public function a_student_who_withdrew_can_still_appeal_a_published_result()
    {
        $e = $this->enrolledWithResult();
        $e->update(['status' => 'dropped']);

        $this->appeal($e->id)->assertSessionHasNoErrors();
    }

    /** @test */
    public function the_enrollment_is_ignored_for_other_request_types()
    {
        $e = $this->enrolledWithResult();

        $this->actingAs($this->studentUser)->post(route('student.service-requests.store'), ['type' => 'enrollment_certificate', 'enrollment_id' => $e->id])->assertSessionHasNoErrors();
        $this->assertNull(StudentServiceRequest::firstOrFail()->enrollment_id);
    }

    /** @test */
    public function the_officer_sees_the_appealed_course_and_processes_the_appeal_without_changing_grades()
    {
        $e = $this->enrolledWithResult();
        $this->appeal($e->id);
        $r = StudentServiceRequest::firstOrFail();

        $this->actingAs($this->officer)->get(route('staff.service-requests.show', $r))->assertOk()
            ->assertSee($e->section->course->name_ar)->assertSee('المقرر المتظلَّم عليه');
        $this->actingAs($this->studentUser)->get(route('student.service-requests.show', $r))->assertOk()->assertSee($e->section->course->name_ar);

        $this->actingAs($this->officer)->put(route('staff.service-requests.update-status', $r), ['status' => 'in_progress'])->assertSessionHasNoErrors();
        $this->actingAs($this->officer)->put(route('staff.service-requests.update-status', $r), ['status' => 'approved', 'staff_notes' => 'أُحيل للمراجعة'])->assertSessionHasNoErrors();
        $this->assertSame('approved', $r->fresh()->status);
        // قبول التظلم لا يغيّر درجة تلقائيًا؛ التصحيح يتم بصلاحية التصحيح المعتمد وبسبب مسجَّل
        $this->assertDatabaseHas('grades', ['enrollment_id' => $e->id, 'score' => 60]);
        $this->assertDatabaseCount('grade_changes', 0);
    }

    /** @test */
    public function the_migration_rolls_back_cleanly()
    {
        $this->assertTrue(\Schema::hasColumn('student_service_requests', 'enrollment_id'));
        $migration = require base_path('database/migrations/2026_11_19_000000_add_enrollment_id_to_student_service_requests_table.php');
        $migration->down();
        $this->assertFalse(\Schema::hasColumn('student_service_requests', 'enrollment_id'));
        $migration->up();
        $this->assertTrue(\Schema::hasColumn('student_service_requests', 'enrollment_id'));
    }
}
