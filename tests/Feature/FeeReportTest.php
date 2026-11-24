<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\FeeSchedule;
use App\Services\FeeReportService;
use App\Services\FeeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AcademicFixtures;
use Tests\TestCase;

class FeeReportTest extends TestCase
{
    use RefreshDatabase, AcademicFixtures;

    private $accountant;
    private $admin;
    private $year;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAcademic();
        $this->accountant = $this->userWithRole('accountant');
        $this->admin = $this->userWithRole('admin', 'admin');
        $this->year = $this->term->academicYear;
    }

    private function feeFor($profile, $amount, $currency = 'USD')
    {
        $schedule = FeeSchedule::firstOrCreate(
            ['department_id' => $this->spec->department_id, 'academic_year_id' => $this->year->id],
            ['amount' => $amount, 'currency' => $currency]
        );

        return app(FeeService::class)->generateFor($profile, $this->year, $this->accountant);
    }

    /** @test */
    public function the_summary_computes_gross_discounts_paid_remaining_and_collection_rate_in_cents()
    {
        [, $p1] = $this->makeStudent();
        [, $p2] = $this->makeStudent();
        [, $p3] = $this->makeStudent();
        $f1 = $this->feeFor($p1, 100.10);
        $f2 = $this->feeFor($p2, 100.10);
        $f3 = $this->feeFor($p3, 100.10);

        $fees = app(FeeService::class);
        $fees->recordPayment($f1, 100.10, now()->toDateString(), null, $this->accountant);          // مسدد
        $fees->addDiscount($f2, 'amount', 0.10, 'خصم رمزي', $this->admin);
        $fees->recordPayment($f2->fresh(), 40.00, now()->toDateString(), null, $this->accountant);  // متبقي 60
        // f3 لم يدفع

        $rows = app(FeeReportService::class)->summary();
        $this->assertCount(1, $rows);
        $r = $rows->first();
        $this->assertSame(3, $r['students']);
        $this->assertSame(1, $r['settled']);
        $this->assertSame(2, $r['unsettled']);
        $this->assertEquals(300.30, $r['gross']);
        $this->assertEquals(0.10, $r['discounts']);
        $this->assertEquals(300.20, $r['net']);
        $this->assertEquals(140.10, $r['paid']);
        $this->assertEquals(160.10, $r['remaining']);
        $this->assertEquals(round(140.10 / 300.20 * 100, 1), $r['collection_rate']);
    }

    /** @test */
    public function voided_payments_do_not_count_as_collected()
    {
        [, $p] = $this->makeStudent();
        $fee = $this->feeFor($p, 100);
        $payment = app(FeeService::class)->recordPayment($fee, 100, now()->toDateString(), null, $this->accountant);
        app(FeeService::class)->voidPayment($payment, 'تحويل لم يصل', $this->admin);

        $r = app(FeeReportService::class)->summary()->first();
        $this->assertEquals(0, $r['paid']);
        $this->assertEquals(100, $r['remaining']);
    }

    /** @test */
    public function currencies_are_never_mixed()
    {
        [, $p1] = $this->makeStudent();
        $this->feeFor($p1, 100, 'USD');

        $spec2 = \App\Models\Specialization::factory()->create();
        $dept2 = Department::find($spec2->department_id);
        $program2 = \App\Models\Program::create(['specialization_id' => $spec2->id, 'level' => 'bachelor']);
        [, $p2] = $this->makeStudent();
        $p2->update(['specialization_id' => $spec2->id, 'program_id' => $program2->id, 'current_level_id' => null]);
        FeeSchedule::create(['department_id' => $dept2->id, 'academic_year_id' => $this->year->id, 'amount' => 50000, 'currency' => 'YER']);
        app(FeeService::class)->generateFor($p2->fresh(), $this->year, $this->accountant);

        $svc = app(FeeReportService::class);
        $totals = $svc->totals($svc->summary());
        $this->assertEquals(100, $totals['USD']['net']);
        $this->assertEquals(50000, $totals['YER']['net']);
        $this->assertCount(2, $svc->summary());
        $this->assertCount(1, $svc->summary(null, $dept2->id));
    }

    /** @test */
    public function the_delinquent_list_has_only_unsettled_sorted_by_largest_remaining()
    {
        [, $a] = $this->makeStudent('أحمد');
        [, $b] = $this->makeStudent('بدر');
        [, $c] = $this->makeStudent('جابر');
        $fa = $this->feeFor($a, 100);
        $fb = $this->feeFor($b, 100);
        $fc = $this->feeFor($c, 100);
        app(FeeService::class)->recordPayment($fa, 100, now()->toDateString(), null, $this->accountant); // مسدد
        app(FeeService::class)->recordPayment($fb, 30, now()->toDateString(), null, $this->accountant);  // متبقي 70

        $list = app(FeeReportService::class)->delinquent();
        $this->assertSame(['جابر', 'بدر'], $list->pluck('student')->all());
        $this->assertEquals([100, 70], $list->pluck('remaining')->all());
    }

    /** @test */
    public function the_page_and_exports_work_for_the_accountant_and_admin_but_not_for_others()
    {
        [, $p] = $this->makeStudent('طالب تقرير');
        $this->feeFor($p, 100);

        foreach ([$this->accountant, $this->admin] as $u) {
            $this->actingAs($u)->get(route('accounting.reports.fees'))->assertOk()->assertSee('طالب تقرير')->assertSee('تقارير الرسوم');
            $r1 = $this->actingAs($u)->get(route('accounting.reports.fees.export'));
            $r1->assertOk();
            $this->assertStringContainsString('spreadsheetml', $r1->headers->get('content-type'));
            $this->actingAs($u)->get(route('accounting.reports.fees.export', ['view' => 'delinquent']))->assertOk();
        }
        foreach ([$this->makeStudent()[0], $this->userWithRole('control'), $this->userWithRole('eservices_officer'), $this->facultyUser] as $u) {
            $this->actingAs($u)->get(route('accounting.reports.fees'))->assertForbidden();
            $this->actingAs($u)->get(route('accounting.reports.fees.export'))->assertForbidden();
        }
    }

    /** @test */
    public function the_report_link_is_in_the_sidebar_of_the_accountant()
    {
        $this->actingAs($this->accountant)->get(route('dashboard'))->assertOk()->assertSee(route('accounting.reports.fees'), false);
    }

    /** @test */
    public function an_empty_system_renders_without_errors()
    {
        $this->actingAs($this->accountant)->get(route('accounting.reports.fees'))->assertOk()->assertSee('لا توجد رسوم مولَّدة');
    }
}
