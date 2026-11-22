<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\FeeSchedule;
use App\Services\FeeService;
use App\Services\FeeStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Support\AcademicFixtures;
use Tests\TestCase;

class PaymentVoidTest extends TestCase
{
    use RefreshDatabase, AcademicFixtures;

    private $accountant;
    private $admin;
    private $studentUser;
    private $profile;
    private $fee;
    private $payment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAcademic();
        $this->accountant = $this->userWithRole('accountant');
        $this->admin = $this->userWithRole('admin', 'admin');
        [$this->studentUser, $this->profile] = $this->makeStudent();
        FeeSchedule::create(['department_id' => $this->spec->department_id, 'academic_year_id' => $this->term->academicYear->id, 'amount' => 100, 'currency' => 'USD']);
        $this->fee = app(FeeService::class)->generateFor($this->profile, $this->term->academicYear, $this->accountant);
        $this->payment = app(FeeService::class)->recordPayment($this->fee, 100, now()->toDateString(), null, $this->accountant);
    }

    /** @test */
    public function voiding_returns_the_amount_to_remaining_keeps_the_receipt_and_logs_it()
    {
        $this->assertTrue($this->fee->fresh()->isSettled());

        $this->actingAs($this->admin)->post(route('payments.void', $this->payment), ['reason' => 'إيصال مكرر بالخطأ'])->assertSessionHasNoErrors();

        $fee = $this->fee->fresh();
        $this->assertSame(0, $fee->paidCents());
        $this->assertSame(10000, $fee->remainingCents());
        $p = $this->payment->fresh();
        $this->assertTrue($p->isVoided());
        $this->assertSame($this->admin->id, $p->voided_by);
        $this->assertNotNull($p->receipt_number);
        $this->assertTrue(ActivityLog::where('action', 'payment.voided')->exists());
        $this->actingAs($this->accountant)->get(route('payments.receipt', $p))->assertOk()->assertSee('سند ملغى');
    }

    /** @test */
    public function a_reason_is_required_and_a_payment_cannot_be_voided_twice()
    {
        $url = route('payments.void', $this->payment);
        $this->actingAs($this->admin)->post($url, [])->assertSessionHasErrors('reason');
        $this->actingAs($this->admin)->post($url, ['reason' => 'abc'])->assertSessionHasErrors('reason');
        $this->assertFalse($this->payment->fresh()->isVoided());

        $this->actingAs($this->admin)->post($url, ['reason' => 'سبب كافٍ'])->assertSessionHasNoErrors();
        $this->actingAs($this->admin)->post($url, ['reason' => 'سبب كافٍ'])->assertSessionHasErrors('payment');
    }

    /** @test */
    public function the_accountant_the_student_and_others_cannot_void_by_default()
    {
        foreach ([$this->accountant, $this->studentUser, $this->userWithRole('control'), $this->userWithRole('eservices_officer')] as $u) {
            $this->actingAs($u)->post(route('payments.void', $this->payment), ['reason' => 'سبب كافٍ'])->assertForbidden();
        }
        $this->assertFalse($this->payment->fresh()->isVoided());
    }

    /** @test */
    public function a_custom_role_holding_the_void_permission_can_void()
    {
        $u = $this->userWithPermissions(['payments.void']);
        $this->actingAs($u)->post(route('payments.void', $this->payment), ['reason' => 'سبب كافٍ'])->assertSessionHasNoErrors();
        $this->assertTrue($this->payment->fresh()->isVoided());
    }

    /** @test */
    public function voiding_a_settled_year_restores_the_results_block_and_allows_repayment()
    {
        $this->assertTrue(app(FeeStatus::class)->withheldYears($this->profile)->isEmpty());

        app(FeeService::class)->voidPayment($this->payment, 'تحويل لم يصل', $this->admin);
        $this->assertCount(1, app(FeeStatus::class)->withheldYears($this->profile));

        // يمكن تسجيل دفعة جديدة بالمبلغ كاملًا بعد الإلغاء، ولا يتجاوز المتبقي
        $again = app(FeeService::class)->recordPayment($this->fee->fresh(), 100, now()->toDateString(), null, $this->accountant);
        $this->assertNotSame($this->payment->receipt_number, $again->receipt_number);
        $this->assertTrue(app(FeeStatus::class)->withheldYears($this->profile)->isEmpty());
    }

    /** @test */
    public function the_void_option_is_shown_only_to_those_who_may_and_the_student_sees_the_status()
    {
        $this->actingAs($this->admin)->get(route('accounting.fees.show', $this->fee))->assertSee('سبب الإلغاء');
        $this->actingAs($this->accountant)->get(route('accounting.fees.show', $this->fee))->assertDontSee('سبب الإلغاء');

        app(FeeService::class)->voidPayment($this->payment, 'سبب كافٍ جدًا', $this->admin);
        $this->actingAs($this->studentUser)->get(route('student.fees.index'))->assertOk()->assertSee('ملغاة');
    }

    /** @test */
    public function the_void_migration_rolls_back_cleanly()
    {
        $migration = require database_path('migrations/2026_11_22_000000_add_void_to_payments_table.php');
        $migration->down();
        $this->assertFalse(Schema::hasColumn('payments', 'voided_at'));
        $this->assertFalse(Schema::hasColumn('payments', 'voided_by'));
        $migration->up();
        $this->assertTrue(Schema::hasColumn('payments', 'void_reason'));
    }
}
