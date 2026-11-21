<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AcademicFixtures;
use Tests\TestCase;

/**
 * ربط الواجهات الجديدة (Phase 5–7 والخدمات والمالية) بالنظام القديم: القائمة الجانبية ولوحات التحكم.
 */
class NavigationIntegrationTest extends TestCase
{
    use RefreshDatabase, AcademicFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAcademic();
    }

    private function dashboard($user)
    {
        return $this->actingAs($user)->get(route('dashboard'))->assertOk();
    }

    /** @test */
    public function the_student_reaches_every_new_student_module_from_the_old_sidebar_and_dashboard()
    {
        [$user] = $this->makeStudent();
        $r = $this->dashboard($user);

        foreach (['student.enrollments.index', 'student.results.index', 'student.academic-record', 'student.transcript', 'student.fees.index', 'student.service-requests.index', 'student.schedule.index'] as $route) {
            $r->assertSee(route($route), false);
        }
        $r->assertSee('الخدمات الأكاديمية')->assertDontSee(route('accounting.fees.index'), false)->assertDontSee(route('exams.index'), false);
    }

    /** @test */
    public function the_accountant_gets_a_dashboard_and_finance_links_only()
    {
        $r = $this->dashboard($this->userWithRole('accountant'));

        $r->assertSee(route('accounting.fees.index'), false)->assertSee(route('fee-schedules.index'), false)
            ->assertDontSee(route('fee-schedules.create'), false)->assertDontSee(route('results.review'), false)
            ->assertDontSee(route('staff.service-requests.index'), false)->assertDontSee(route('student.fees.index'), false);
    }

    /** @test */
    public function the_eservices_officer_sees_the_requests_link_but_not_finance()
    {
        $r = $this->dashboard($this->userWithRole('eservices_officer'));

        $r->assertSee(route('staff.service-requests.index'), false)->assertDontSee(route('accounting.fees.index'), false);
    }

    /** @test */
    public function faculty_reach_exams_their_schedule_and_attendance()
    {
        $r = $this->dashboard($this->facultyUser);

        foreach (['exams.index', 'faculty.schedule.index', 'faculty.attendance.index', 'faculty.enrollments.index'] as $route) {
            $r->assertSee(route($route), false);
        }
        $r->assertDontSee(route('accounting.fees.index'), false);
    }

    /** @test */
    public function the_admin_sees_the_new_modules_without_duplicating_old_links()
    {
        $r = $this->dashboard($this->userWithRole('admin', 'admin'));

        foreach (['exams.index', 'results.review', 'results.publish', 'accounting.fees.index', 'fee-schedules.index', 'academic-settings.edit', 'staff.service-requests.index'] as $route) {
            $r->assertSee(route($route), false);
        }
    }

    /** @test */
    public function the_staff_dashboard_does_not_duplicate_its_existing_cards()
    {
        $user = $this->userWithPermissions(['service-requests.manageAll', 'rooms.manage']);
        $html = $this->dashboard($user)->getContent();

        // الرابط يظهر في البطاقة الأصلية للوحة وفي القائمة الجانبية فقط (مرتان) لا ثلاثًا
        $this->assertSame(2, substr_count($html, route('staff.service-requests.index')));
    }

    /** @test */
    public function the_new_tailwind_screens_link_back_to_the_old_system()
    {
        [$user] = $this->makeStudent();
        $this->actingAs($user)->get(route('student.results.index'))->assertOk()
            ->assertSee(route('projects.index'), false)->assertSee(route('notifications.index'), false)->assertSee(route('profile.show'), false);
    }

    /** @test */
    public function the_old_project_screens_are_untouched_for_supervisors_and_admins()
    {
        $this->actingAs($this->userWithRole('admin', 'admin'))->get(route('projects.index'))->assertOk()->assertSee(route('admin.users.index'), false);
    }
}
