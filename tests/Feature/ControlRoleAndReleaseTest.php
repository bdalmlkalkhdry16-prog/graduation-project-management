<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\FeeSchedule;
use App\Models\Role;
use App\Services\FeeService;
use App\Services\FeeStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Support\AcademicFixtures;
use Tests\TestCase;

class ControlRoleAndReleaseTest extends TestCase
{
    use RefreshDatabase, AcademicFixtures;

    private $accountant;
    private $admin;
    private $profile;
    private $studentUser;
    private $fee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAcademic();
        $this->accountant = $this->userWithRole('accountant');
        $this->admin = $this->userWithRole('admin', 'admin');
        [$this->studentUser, $this->profile] = $this->makeStudent();
        $year = $this->term->academicYear;
        FeeSchedule::create(['department_id' => $this->spec->department_id, 'academic_year_id' => $year->id, 'amount' => 100, 'currency' => 'USD']);
        $this->fee = app(FeeService::class)->generateFor($this->profile, $year, $this->accountant);
    }

    /** @test */
    public function only_the_admin_can_release_and_it_requires_a_reason()
    {
        $url = route('accounting.fees.release-results', $this->fee);

        foreach ([$this->accountant, $this->studentUser, $this->userWithRole('control'), $this->userWithRole('eservices_officer')] as $u) {
            $this->actingAs($u)->post($url, ['release' => 1, 'reason' => 'سبب كافٍ'])->assertForbidden();
        }
        $this->assertFalse($this->fee->fresh()->resultsReleased());

        $this->actingAs($this->admin)->post($url, ['release' => 1])->assertSessionHasErrors('reason');
        $this->assertFalse($this->fee->fresh()->resultsReleased());
    }

    /** @test */
    public function a_release_lifts_the_block_without_touching_the_amounts_and_can_be_revoked()
    {
        $this->assertCount(1, app(FeeStatus::class)->withheldYears($this->profile));

        $this->actingAs($this->admin)->post(route('accounting.fees.release-results', $this->fee), ['release' => 1, 'reason' => 'منحة دراسية معتمدة'])->assertSessionHasNoErrors();
        $fee = $this->fee->fresh();
        $this->assertTrue($fee->resultsReleased());
        $this->assertSame($this->admin->id, $fee->results_released_by);
        $this->assertSame(10000, $fee->remainingCents());
        $this->assertTrue(app(FeeStatus::class)->withheldYears($this->profile)->isEmpty());
        $this->assertTrue(ActivityLog::where('action', 'student_fee.results_released')->exists());

        $this->actingAs($this->admin)->post(route('accounting.fees.release-results', $fee), ['release' => 0])->assertSessionHasNoErrors();
        $this->assertCount(1, app(FeeStatus::class)->withheldYears($this->profile));
        $this->assertTrue(ActivityLog::where('action', 'student_fee.results_release_revoked')->exists());
    }

    /** @test */
    public function the_release_form_is_shown_to_the_admin_only()
    {
        $this->actingAs($this->admin)->get(route('accounting.fees.show', $this->fee))->assertOk()->assertSee('رفع الحجب');
        $this->actingAs($this->accountant)->get(route('accounting.fees.show', $this->fee))->assertOk()->assertDontSee('رفع الحجب');
    }

    /** @test */
    public function the_control_role_runs_the_results_cycle_but_not_finance_or_academic_policy()
    {
        $role = Role::where('slug', 'control')->firstOrFail();
        $this->assertEqualsCanonicalizing(
            ['exams.manage', 'results.review', 'results.publish', 'grades.correct', 'exams.adjustDistribution', 'academic-records.viewAll'],
            $role->permissions()->pluck('slug')->all()
        );

        $control = $this->userWithRole('control');
        $this->actingAs($control)->get(route('results.review'))->assertOk();
        $this->actingAs($control)->get(route('results.publish'))->assertOk();
        $this->actingAs($control)->get(route('accounting.fees.index'))->assertForbidden();
        $this->actingAs($control)->get(route('fee-schedules.index'))->assertForbidden();
        $this->actingAs($control)->get(route('academic-settings.edit'))->assertForbidden();
        $this->actingAs($control)->get(route('dashboard'))->assertOk()->assertSee('مراجعة النتائج')->assertSee('نشر النتائج');
    }

    /** @test */
    public function the_release_migration_rolls_back_cleanly()
    {
        $this->assertTrue(Schema::hasColumn('student_fees', 'results_released_at'));
        $migration = require database_path('migrations/2026_11_21_000000_add_results_release_to_student_fees_table.php');
        $migration->down();
        $this->assertFalse(Schema::hasColumn('student_fees', 'results_released_at'));
        $this->assertFalse(Schema::hasColumn('student_fees', 'results_released_by'));
        $migration->up();
        $this->assertTrue(Schema::hasColumn('student_fees', 'results_release_reason'));
    }
}
