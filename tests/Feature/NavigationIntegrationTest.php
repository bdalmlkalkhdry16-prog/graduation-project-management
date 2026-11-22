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

        foreach (['exams.index', 'results.review', 'results.publish', 'accounting.fees.index', 'fee-schedules.index', 'academic-settings.edit'] as $route) {
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
    public function the_new_screens_render_inside_the_old_layout_with_its_sidebar_and_bootstrap_styling()
    {
        [$student] = $this->makeStudent();
        $admin = $this->userWithRole('admin', 'admin');
        $section = $this->section;
        $exam = $this->makeExam($section);
        $pages = [
            [$student, route('student.results.index')],
            [$student, route('student.academic-record')],
            [$student, route('student.transcript')],
            [$student, route('student.fees.index')],
            [$student, route('student.enrollments.index')],
            [$this->facultyUser, route('faculty.enrollments.index')],
            [$this->facultyUser, route('faculty.enrollments.show', $section)],
            [$this->facultyUser, route('exams.index')],
            [$this->facultyUser, route('exams.show', $exam)],
            [$this->facultyUser, route('exams.grades.edit', $exam)],
            [$this->facultyUser, route('exams.create')],
            [$admin, route('results.review')],
            [$admin, route('results.publish')],
            [$admin, route('academic-settings.edit')],
            [$admin, route('fee-schedules.index')],
            [$admin, route('fee-schedules.create')],
            [$admin, route('accounting.fees.index')],
            [$this->userWithRole('student_affairs'), route('staff.enrollments.index')],
            [$this->userWithRole('student_affairs'), route('staff.enrollments.students')],
            [$this->userWithRole('student_affairs'), route('staff.enrollments.show', $section)],
            [$admin, route('exams.edit', $exam)],
        ];
        foreach ($pages as [$user, $url]) {
            $response = $this->actingAs($user)->get($url);
            $this->assertSame(200, $response->getStatusCode(), "$url => ".$response->getStatusCode());
            $html = $response->getContent();
            $this->assertStringContainsString('id="sidebar"', $html, "$url خارج تخطيط النظام");
            $this->assertStringContainsString('bootstrap.rtl.min.css', $html, $url);
            $this->assertStringNotContainsString('bg-slate-', $html, "$url ما زال بتنسيق Tailwind");
            $this->assertStringNotContainsString('text-indigo-', $html, "$url ما زال بتنسيق Tailwind");
        }
        $this->assertFileDoesNotExist(resource_path('views/layouts/tailwind.blade.php'));
    }

    /** @test */
    public function the_old_sidebar_links_to_the_new_modules_for_a_student()
    {
        [$user] = $this->makeStudent();
        $this->actingAs($user)->get(route('student.results.index'))->assertOk()
            ->assertSee(route('projects.index'), false)->assertSee(route('student.fees.index'), false)->assertSee(route('student.transcript'), false);
    }

    /** @test */
    public function pagination_uses_bootstrap_markup()
    {
        \App\Models\User::factory()->count(3)->create();
        $this->assertStringContainsString('class="pagination"', (string) \App\Models\User::paginate(1)->links());
    }

    /** @test */
    public function the_old_project_screens_are_untouched_for_supervisors_and_admins()
    {
        $this->actingAs($this->userWithRole('admin', 'admin'))->get(route('projects.index'))->assertOk()->assertSee(route('admin.users.index'), false);
    }

    /** @test */
    public function the_admin_has_every_permission_so_all_management_screens_open_and_are_linked()
    {
        foreach ([$this->userWithRole('admin', 'admin'), \App\Models\User::factory()->admin()->create()] as $admin) {
            $html = $this->actingAs($admin)->get(route('dashboard'))->assertOk()->getContent();
            foreach (['staff.enrollments.index', 'staff.rooms.index', 'staff.schedules.index', 'staff.attendance.index', 'staff.student-profiles.index', 'staff.service-requests.index', 'results.review', 'accounting.fees.index'] as $name) {
                $this->assertStringContainsString(route($name), $html, "$name غير ظاهر للمدير");
                $this->assertSame(200, $this->actingAs($admin)->get(route($name))->getStatusCode(), "$name للمدير");
            }
        }
    }

    /** @test */
    public function the_admin_never_hits_a_server_error_on_any_permission_protected_page()
    {
        $admin = $this->userWithRole('admin', 'admin');
        $skip = ['logout'];
        foreach (\Illuminate\Support\Facades\Route::getRoutes() as $route) {
            if (! in_array('GET', $route->methods()) || ! in_array('auth', $route->gatherMiddleware()) || str_contains($route->uri(), '{')) {
                continue;
            }
            $guarded = collect($route->gatherMiddleware())->contains(fn ($m) => is_string($m) && str_starts_with($m, 'permission:'));
            if (! $guarded || in_array($route->getName(), $skip)) {
                continue;
            }
            $status = $this->actingAs($admin)->get('/'.$route->uri())->getStatusCode();
            $this->assertLessThan(500, $status, $route->uri().' => '.$status);
            // صفحات «الخاصة» (my/*، شعبي، حضوري...) للطالب/المدرس نفسه؛ أما الإدارية فمفتوحة للمدير
            $own = collect($route->gatherMiddleware())->contains(fn ($m) => is_string($m) && str_starts_with($m, 'permission:') && str_contains($m, 'Own'));
            if (! $own) {
                $this->assertNotSame(403, $status, $route->uri().' مغلق على المدير');
            }
        }
    }
}
