<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\StudentServiceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AcademicFixtures;
use Tests\TestCase;

/**
 * E1 — الخدمات الإلكترونية: كتالوج الأنواع، دور موظف البوابة، مسار الحالات.
 */
class ServiceRequestTest extends TestCase
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

    private function submit(string $type = 'enrollment_certificate', $user = null)
    {
        return $this->actingAs($user ?? $this->studentUser)->post(route('student.service-requests.store'), ['type' => $type, 'description' => 'ملاحظة']);
    }

    private function request(string $status = 'pending', string $type = 'enrollment_certificate'): StudentServiceRequest
    {
        return StudentServiceRequest::create(['student_profile_id' => $this->profile->id, 'type' => $type, 'status' => $status]);
    }

    private function setStatus(StudentServiceRequest $r, string $status, array $extra = [])
    {
        return $this->actingAs($this->officer)->put(route('staff.service-requests.update-status', $r), ['status' => $status] + $extra);
    }

    /** @test */
    public function the_student_can_submit_each_available_type_and_sees_it_with_its_arabic_name()
    {
        foreach (['enrollment_certificate', 'graduation_statement', 're_enrollment'] as $type) {
            $this->submit($type)->assertSessionHasNoErrors();
            $this->assertDatabaseHas('student_service_requests', ['student_profile_id' => $this->profile->id, 'type' => $type, 'status' => 'pending']);
        }

        $this->actingAs($this->studentUser)->get(route('student.service-requests.index'))->assertOk()
            ->assertSee('شهادة قيد')->assertSee('إفادة تخرج')->assertSee('إعادة قيد');
    }

    /** @test */
    public function free_text_unknown_and_removed_types_are_rejected()
    {
        foreach (['كشف درجات حر', 'deferral', 'semester_deferral', 'xyz'] as $type) {
            $this->submit($type)->assertSessionHasErrors('type');
        }
        $this->assertSame(0, StudentServiceRequest::count());
        $this->assertArrayNotHasKey('semester_deferral', StudentServiceRequest::TYPES);
        $this->assertStringNotContainsString('تأجيل', implode(' ', StudentServiceRequest::TYPES));
    }

    /** @test */
    public function transcript_and_appeal_stay_closed_until_their_own_phases()
    {
        $this->submit('transcript')->assertSessionHasErrors('type');
        $this->submit('grade_appeal')->assertSessionHasErrors('type');
        $this->assertSame(0, StudentServiceRequest::count());

        $this->actingAs($this->studentUser)->get(route('student.service-requests.create'))->assertOk()
            ->assertSee('شهادة قيد')->assertDontSee('كشف درجات')->assertDontSee('تظلم');
    }

    /** @test */
    public function a_second_open_request_of_the_same_type_is_blocked_until_the_first_is_closed()
    {
        $this->submit('re_enrollment')->assertSessionHasNoErrors();
        $this->submit('re_enrollment')->assertSessionHasErrors('type');
        $this->assertSame(1, StudentServiceRequest::count());

        StudentServiceRequest::first()->update(['status' => 'rejected']);
        $this->submit('re_enrollment')->assertSessionHasNoErrors();
        $this->assertSame(2, StudentServiceRequest::count());
    }

    /** @test */
    public function a_user_without_a_student_profile_cannot_submit()
    {
        $this->submit('enrollment_certificate', $this->userWithRole('staff'))->assertForbidden();
        $this->assertSame(0, StudentServiceRequest::count());
    }

    /** @test */
    public function the_student_sees_only_their_own_requests()
    {
        [$otherUser, $other] = $this->makeStudent();
        $mine = $this->request();
        $theirs = StudentServiceRequest::create(['student_profile_id' => $other->id, 'type' => 're_enrollment']);

        $this->actingAs($this->studentUser)->get(route('student.service-requests.show', $mine))->assertOk();
        $this->actingAs($this->studentUser)->get(route('student.service-requests.show', $theirs))->assertForbidden();
        $this->actingAs($this->studentUser)->get(route('student.service-requests.index'))->assertOk()->assertDontSee('إعادة قيد');
    }

    /** @test */
    public function the_eservices_officer_role_holds_only_the_request_management_permission()
    {
        $role = Role::where('slug', 'eservices_officer')->firstOrFail();

        $this->assertTrue($role->is_system);
        $this->assertSame(['service-requests.manageAll'], $role->permissions()->pluck('slug')->all());
    }

    /** @test */
    public function the_officer_processes_requests_while_students_faculty_and_guests_cannot()
    {
        $r = $this->request();

        $this->actingAs($this->officer)->get(route('staff.service-requests.index'))->assertOk()->assertSee('شهادة قيد');
        $this->actingAs($this->officer)->get(route('staff.service-requests.show', $r))->assertOk();
        $this->setStatus($r, 'approved')->assertSessionHasNoErrors();
        $this->assertSame($this->officer->id, $r->fresh()->handled_by);

        foreach ([$this->studentUser, $this->facultyUser, $this->userWithRole('student_affairs')] as $user) {
            $this->actingAs($user)->get(route('staff.service-requests.index'))->assertForbidden();
            $this->actingAs($user)->put(route('staff.service-requests.update-status', $r), ['status' => 'completed'])->assertForbidden();
        }
        auth()->logout();
        $this->get(route('staff.service-requests.index'))->assertRedirect();
        $this->assertSame('approved', $r->fresh()->status);
    }

    /** @test */
    public function the_officer_lands_on_the_staff_dashboard_with_the_requests_card()
    {
        $this->actingAs($this->officer)->get(route('dashboard'))->assertOk()->assertSee(route('staff.service-requests.index'));
    }

    /** @test */
    public function allowed_status_transitions_follow_the_workflow()
    {
        $ok = [['pending', 'in_progress'], ['pending', 'approved'], ['pending', 'rejected'], ['in_progress', 'approved'], ['in_progress', 'rejected'], ['approved', 'completed']];

        foreach ($ok as [$from, $to]) {
            $r = $this->request($from);
            $this->setStatus($r, $to, ['staff_notes' => 'سبب'])->assertSessionHasNoErrors();
            $this->assertSame($to, $r->fresh()->status, "$from → $to");
        }
    }

    /** @test */
    public function forbidden_transitions_and_final_states_are_blocked()
    {
        $bad = [['pending', 'completed'], ['pending', 'pending'], ['in_progress', 'pending'], ['in_progress', 'completed'], ['approved', 'rejected'], ['approved', 'in_progress'],
                ['rejected', 'approved'], ['rejected', 'pending'], ['completed', 'pending'], ['completed', 'approved']];

        foreach ($bad as [$from, $to]) {
            $r = $this->request($from);
            $this->setStatus($r, $to, ['staff_notes' => 'x'])->assertSessionHasErrors('status');
            $this->assertSame($from, $r->fresh()->status, "$from ↛ $to");
        }
    }

    /** @test */
    public function rejection_requires_a_reason_in_the_notes()
    {
        $r = $this->request();

        $this->setStatus($r, 'rejected')->assertSessionHasErrors('staff_notes');
        $this->assertSame('pending', $r->fresh()->status);

        $this->setStatus($r, 'rejected', ['staff_notes' => 'الوثائق ناقصة'])->assertSessionHasNoErrors();
        $this->assertSame('rejected', $r->fresh()->status);
        $this->assertSame('الوثائق ناقصة', $r->fresh()->staff_notes);
    }

    /** @test */
    public function legacy_free_text_requests_still_display_and_can_be_processed()
    {
        $legacy = StudentServiceRequest::create(['student_profile_id' => $this->profile->id, 'type' => 'نص قديم حر', 'status' => 'pending']);

        $this->actingAs($this->officer)->get(route('staff.service-requests.show', $legacy))->assertOk()->assertSee('نص قديم حر');
        $this->actingAs($this->studentUser)->get(route('student.service-requests.index'))->assertOk()->assertSee('نص قديم حر');
        $this->setStatus($legacy, 'in_progress')->assertSessionHasNoErrors();
    }
}
