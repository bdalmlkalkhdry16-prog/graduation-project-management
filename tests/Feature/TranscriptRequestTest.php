<?php

namespace Tests\Feature;

use App\Models\AcademicSetting;
use App\Models\Permission;
use App\Models\Role;
use App\Models\StudentServiceRequest;
use App\Models\StudyPlan;
use App\Services\StudyPlanCompletion;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AcademicFixtures;
use Tests\TestCase;

/**
 * E2 — كشف الدرجات الرسمي: يُطلب بعد إتمام الخطة الدراسية (دبلوم/بكالوريوس).
 */
class TranscriptRequestTest extends TestCase
{
    use RefreshDatabase, AcademicFixtures;

    private $studentUser;
    private $profile;
    private $officer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAcademic();
        AcademicSetting::put(AcademicSetting::PASS_PERCENTAGE, 50);
        [$this->studentUser, $this->profile] = $this->makeStudent();
        $this->officer = $this->userWithRole('eservices_officer');
    }

    /** يسجّل الطالب في شعبة لمقرر ويمنحه نتيجة منشورة بنسبة معينة. */
    private function withResult($course, float $percent, $profile = null)
    {
        $section = $this->makeSection($this->faculty, $course);
        $enrollment = $this->enroll($profile ?? $this->profile, $section);
        $exam = $this->makeExam($section, ['max_score' => 100, 'weight' => 100, 'result_status' => 'published', 'published_at' => now()]);
        $this->setGrade($exam, $enrollment, $percent);

        return $enrollment;
    }

    private function request(string $status = 'pending'): StudentServiceRequest
    {
        return StudentServiceRequest::create(['student_profile_id' => $this->profile->id, 'type' => 'transcript', 'status' => $status]);
    }

    private function submit()
    {
        return $this->actingAs($this->studentUser)->post(route('student.service-requests.store'), ['type' => 'transcript']);
    }

    /** @test */
    public function a_student_who_has_not_finished_the_plan_cannot_request_a_transcript()
    {
        $this->withResult($this->course, 80);          // C1 ناجح
        $this->makeCourse('C2');                    // مقرر إلزامي آخر لم يُدرس

        $this->submit()->assertSessionHasErrors('type');
        $this->assertSame(0, StudentServiceRequest::count());

        $check = app(StudyPlanCompletion::class)->check($this->profile);
        $this->assertSame('plan_incomplete', $check['reason']);
        $this->assertSame(['C2'], $check['missing']->pluck('code')->all());
    }

    /** @test */
    public function a_failed_course_blocks_the_request()
    {
        $this->withResult($this->course, 30);

        $this->submit()->assertSessionHasErrors('type');
    }

    /** @test */
    public function a_student_who_passed_every_mandatory_course_can_request_a_transcript()
    {
        $c2 = $this->makeCourse('C2');
        $this->withResult($this->course, 80);
        $this->withResult($c2, 55);

        $this->submit()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('student_service_requests', ['student_profile_id' => $this->profile->id, 'type' => 'transcript', 'status' => 'pending']);
        $this->assertTrue(app(StudyPlanCompletion::class)->check($this->profile)['eligible']);
    }

    /** @test */
    public function optional_courses_are_not_required_and_a_passed_retake_counts()
    {
        $optional = $this->makeCourse('OPT');
        StudyPlan::where('course_id', $optional->id)->update(['is_mandatory' => false]);

        $this->withResult($this->course, 20);   // رسب
        $this->withResult($this->course, 70);   // أعاد ونجح

        $this->assertTrue(app(StudyPlanCompletion::class)->check($this->profile)['eligible']);
        $this->submit()->assertSessionHasNoErrors();
    }

    /** @test */
    public function without_a_configured_pass_percentage_the_plan_cannot_be_judged()
    {
        AcademicSetting::where('key', AcademicSetting::PASS_PERCENTAGE)->delete();
        $this->withResult($this->course, 99);

        $this->submit()->assertSessionHasErrors('type');
        $this->assertSame('pass_percentage_missing', app(StudyPlanCompletion::class)->check($this->profile)['reason']);
    }

    /** @test */
    public function a_student_without_a_program_cannot_request_a_transcript()
    {
        $this->withResult($this->course, 99);
        $this->profile->forceFill(['program_id' => null, 'current_level_id' => null])->saveQuietly();

        $this->assertSame('no_program', app(StudyPlanCompletion::class)->check($this->profile->fresh())['reason']);
    }

    /** @test */
    public function approval_rechecks_that_the_student_still_completed_the_plan()
    {
        $this->withResult($this->course, 80);
        $r = $this->request();
        $this->makeCourse('C9');   // أُضيف مقرر إلزامي جديد للخطة بعد تقديم الطلب

        $this->actingAs($this->officer)->put(route('staff.service-requests.update-status', $r), ['status' => 'approved'])->assertSessionHasErrors('status');
        $this->assertSame('pending', $r->fresh()->status);
    }

    /** @test */
    public function the_officer_approves_an_eligible_request_and_the_pdf_is_available_to_both_sides()
    {
        $this->withResult($this->course, 80);
        $r = $this->request();

        // قبل القبول لا يوجد كشف
        $this->actingAs($this->studentUser)->get(route('student.service-requests.transcript', $r))->assertForbidden();
        $this->actingAs($this->officer)->get(route('staff.service-requests.transcript', $r))->assertForbidden();

        $this->actingAs($this->officer)->put(route('staff.service-requests.update-status', $r), ['status' => 'approved'])->assertSessionHasNoErrors();

        foreach ([[$this->studentUser, 'student.service-requests.transcript'], [$this->officer, 'staff.service-requests.transcript']] as [$user, $route]) {
            $response = $this->actingAs($user)->get(route($route, $r))->assertOk();
            $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
            $this->assertStringStartsWith('%PDF', $response->getContent());
        }

        $r->update(['status' => 'completed']);
        $this->actingAs($this->studentUser)->get(route('student.service-requests.transcript', $r))->assertOk();
    }

    /** @test */
    public function the_transcript_is_not_available_to_other_students_or_for_other_request_types_or_rejected_requests()
    {
        $this->withResult($this->course, 80);
        $r = $this->request('approved');
        [$otherUser] = $this->makeStudent();

        $this->actingAs($otherUser)->get(route('student.service-requests.transcript', $r))->assertForbidden();
        $this->actingAs($this->facultyUser)->get(route('staff.service-requests.transcript', $r))->assertForbidden();

        $r->update(['status' => 'rejected']);
        $this->actingAs($this->studentUser)->get(route('student.service-requests.transcript', $r))->assertForbidden();

        $cert = StudentServiceRequest::create(['student_profile_id' => $this->profile->id, 'type' => 'enrollment_certificate', 'status' => 'approved']);
        $this->actingAs($this->studentUser)->get(route('student.service-requests.transcript', $cert))->assertForbidden();
        $this->actingAs($this->officer)->get(route('staff.service-requests.transcript', $cert))->assertForbidden();
    }

    /** @test */
    public function the_general_staff_role_no_longer_manages_service_requests_even_on_an_existing_database()
    {
        // محاكاة قاعدة قائمة منحت الصلاحية لدور staff سابقًا
        $staff = Role::where('slug', 'staff')->first();
        $staff->permissions()->attach(Permission::where('slug', 'service-requests.manageAll')->first());
        $this->assertTrue($staff->permissions()->where('slug', 'service-requests.manageAll')->exists());

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertFalse($staff->permissions()->where('slug', 'service-requests.manageAll')->exists());
        $this->assertTrue($staff->permissions()->where('slug', 'student-profiles.manage')->exists());
        $this->actingAs($this->userWithRole('staff'))->get(route('staff.service-requests.index'))->assertForbidden();
        $this->actingAs($this->officer)->get(route('staff.service-requests.index'))->assertOk();
    }
}
