<?php

namespace Tests\Feature;

use App\Models\StudentServiceRequest;
use App\Models\User;
use App\Services\DashboardChartsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AcademicFixtures;
use Tests\TestCase;

class AdminDashboardChartsTest extends TestCase
{
    use RefreshDatabase, AcademicFixtures;

    public function test_empty_system_renders_dashboard_with_empty_charts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()->assertSee('chartRequests', false)->assertSee('لا توجد طلبات.');
    }

    public function test_charts_use_real_counts(): void
    {
        $this->setUpAcademic();
        [, $s] = $this->makeStudent();
        StudentServiceRequest::create([
            'student_profile_id' => $s->id,
            'type' => StudentServiceRequest::TYPE_ENROLLMENT_CERTIFICATE,
            'status' => StudentServiceRequest::STATUS_PENDING,
        ]);
        $c = app(DashboardChartsService::class)->forAdmin();
        $this->assertSame(1, $c['open_requests']);
        $this->assertSame(1, array_sum($c['line']['values']));
        $this->assertSame(6, count($c['line']['labels']));
        $this->assertSame(1, array_sum($c['donut']['values']));

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertSee('chartSpecs', false);
    }
}
