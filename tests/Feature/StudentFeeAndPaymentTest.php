<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\FeeSchedule;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Specialization;
use App\Models\StudentFee;
use App\Services\FeeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Support\AcademicFixtures;
use Tests\TestCase;

/**
 * F1b — رسوم الطالب والمدفوعات وسندات القبض.
 */
class StudentFeeAndPaymentTest extends TestCase
{
    use RefreshDatabase, AcademicFixtures;

    private $accountant;
    private $year;
    private FeeSchedule $schedule;
    private $studentUser;
    private $profile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAcademic();
        $this->year = $this->term->academicYear;
        $this->accountant = $this->userWithRole('accountant');
        $this->schedule = FeeSchedule::create(['department_id' => $this->spec->department_id, 'academic_year_id' => $this->year->id, 'amount' => 100.10, 'currency' => 'USD']);
        [$this->studentUser, $this->profile] = $this->makeStudent();
    }

    private function fee(): StudentFee
    {
        return app(FeeService::class)->generateFor($this->profile, $this->year, $this->accountant);
    }

    private function pay(StudentFee $fee, $amount, array $extra = [], $user = null)
    {
        return $this->actingAs($user ?? $this->accountant)->post(route('accounting.payments.store', $fee), array_merge(['amount' => $amount, 'paid_at' => now()->toDateString()], $extra));
    }

    /** @test */
    public function generation_creates_a_pinned_copy_for_active_students_only_and_is_idempotent()
    {
        [, $suspended] = $this->makeStudent();
        $suspended->forceFill(['academic_status' => 'suspended'])->saveQuietly();

        $this->actingAs($this->accountant)->post(route('accounting.fees.generate'), ['academic_year_id' => $this->year->id])->assertSessionHasNoErrors();

        $this->assertSame(1, StudentFee::count());
        $fee = StudentFee::firstOrFail();
        $this->assertSame($this->profile->id, $fee->student_profile_id);
        $this->assertEquals(100.10, $fee->amount);
        $this->assertSame('USD', $fee->currency);
        $this->assertSame($this->schedule->id, $fee->fee_schedule_id);

        $this->actingAs($this->accountant)->post(route('accounting.fees.generate'), ['academic_year_id' => $this->year->id])->assertSessionHasNoErrors();
        $this->assertSame(1, StudentFee::count());
    }

    /** @test */
    public function a_department_without_a_price_gets_no_fee_and_nothing_is_guessed()
    {
        $otherSpec = Specialization::factory()->create();
        [, $other] = $this->makeStudent();
        $other->forceFill(['specialization_id' => $otherSpec->id, 'program_id' => null, 'current_level_id' => null])->saveQuietly();

        $this->actingAs($this->accountant)->post(route('accounting.fees.generate'), ['academic_year_id' => $this->year->id]);

        $this->assertSame(1, StudentFee::count());
        $this->assertFalse(StudentFee::where('student_profile_id', $other->id)->exists());
        $this->expectException(ValidationException::class);
        app(FeeService::class)->generateFor($other->fresh(), $this->year, $this->accountant);
    }

    /** @test */
    public function changing_the_price_later_does_not_change_the_amount_owed_by_generated_students()
    {
        $fee = $this->fee();
        $manager = $this->userWithPermissions(['fees.manage']);

        $this->actingAs($manager)->put(route('fee-schedules.update', $this->schedule), [
            'department_id' => $this->schedule->department_id, 'academic_year_id' => $this->year->id, 'amount' => 999, 'currency' => 'YER',
        ])->assertSessionHasNoErrors();

        $this->assertEquals(999, $this->schedule->fresh()->amount);
        $this->assertEquals(100.10, $fee->fresh()->amount);
        $this->assertSame('USD', $fee->fresh()->currency);
    }

    /** @test */
    public function a_price_that_generated_fees_cannot_be_deleted_or_moved()
    {
        $this->fee();
        $manager = $this->userWithPermissions(['fees.manage']);
        $other = \App\Models\Department::factory()->create();

        $this->actingAs($manager)->delete(route('fee-schedules.destroy', $this->schedule))->assertSessionHasErrors('fee');
        $this->assertDatabaseHas('fee_schedules', ['id' => $this->schedule->id]);

        $this->actingAs($manager)->put(route('fee-schedules.update', $this->schedule), [
            'department_id' => $other->id, 'academic_year_id' => $this->year->id, 'amount' => 100.10, 'currency' => 'USD',
        ])->assertSessionHasErrors('department_id');
    }

    /** @test */
    public function partial_payments_accumulate_with_exact_cents_until_settled()
    {
        $fee = $this->fee();   // 100.10

        $this->pay($fee, 50.05)->assertSessionHasNoErrors();
        $this->assertFalse($fee->fresh()->isSettled());
        $this->assertSame(5005, $fee->fresh()->remainingCents());

        $this->pay($fee, 50.05)->assertSessionHasNoErrors();
        $fee = $fee->fresh();
        $this->assertTrue($fee->isSettled());
        $this->assertSame(0, $fee->remainingCents());
        $this->assertSame(2, $fee->payments()->count());
    }

    /** @test */
    public function an_overpayment_a_zero_amount_and_a_future_date_are_rejected()
    {
        $fee = $this->fee();

        $this->pay($fee, 100.11)->assertSessionHasErrors('amount');
        $this->pay($fee, 0)->assertSessionHasErrors('amount');
        $this->pay($fee, -5)->assertSessionHasErrors('amount');
        $this->pay($fee, 10.555)->assertSessionHasErrors('amount');
        $this->pay($fee, 10, ['paid_at' => now()->addDay()->toDateString()])->assertSessionHasErrors('paid_at');
        $this->assertSame(0, Payment::count());

        $this->pay($fee, 100.10)->assertSessionHasNoErrors();
        $this->pay($fee, 0.01)->assertSessionHasErrors('amount');   // لا شيء متبقٍ
        $this->assertSame(1, Payment::count());
    }

    /** @test */
    public function every_payment_gets_a_unique_receipt_number_and_is_logged()
    {
        $fee = $this->fee();
        $this->pay($fee, 20);
        $this->pay($fee, 30);

        $receipts = Payment::orderBy('id')->pluck('receipt_number')->all();
        $this->assertCount(2, array_unique($receipts));
        $this->assertMatchesRegularExpression('/^RC-\d{6}$/', $receipts[0]);
        $this->assertSame($this->accountant->id, Payment::first()->recorded_by);
        $this->assertSame(2, ActivityLog::where('action', 'payment.recorded')->count());
        $this->assertSame(1, ActivityLog::where('action', 'student_fee.generated')->count());
    }

    /** @test */
    public function only_the_accountant_role_can_generate_and_record_payments()
    {
        $fee = $this->fee();

        foreach ([$this->studentUser, $this->facultyUser, $this->userWithRole('eservices_officer'), $this->userWithRole('staff'), $this->userWithRole('student_affairs')] as $user) {
            $this->pay($fee, 10, [], $user)->assertForbidden();
            $this->actingAs($user)->post(route('accounting.fees.generate'), ['academic_year_id' => $this->year->id])->assertForbidden();
            $this->actingAs($user)->get(route('accounting.fees.index'))->assertForbidden();
        }
        $this->assertSame(0, Payment::count());
        auth()->logout();
        $this->get(route('accounting.fees.index'))->assertRedirect();
    }

    /** @test */
    public function the_accountant_role_holds_only_finance_permissions_and_the_student_can_view_own_fees()
    {
        $perms = Role::where('slug', 'accountant')->firstOrFail()->permissions()->pluck('slug')->sort()->values()->all();
        $this->assertSame(['fees.generate', 'fees.view', 'payments.record'], $perms);
        $this->assertTrue(Role::where('slug', 'student')->firstOrFail()->permissions()->where('slug', 'fees.viewOwn')->exists());
        $this->assertFalse($this->accountant->hasPermission('fees.manage'));
        $this->assertFalse($this->accountant->hasPermission('grades.correct'));
    }

    /** @test */
    public function the_student_sees_only_their_own_fees_and_receipts()
    {
        // بالخدمة مباشرة: طلب HTTP للمحاسب يترك رسالة نجاح في الجلسة المشتركة بين طلبات الاختبار
        $fee = $this->fee();
        app(FeeService::class)->recordPayment($fee, 40, now()->toDateString(), null, $this->accountant);
        [$otherUser, $other] = $this->makeStudent();
        $otherFee = app(FeeService::class)->generateFor($other, $this->year, $this->accountant);
        app(FeeService::class)->recordPayment($otherFee, 25, now()->toDateString(), null, $this->accountant);

        $this->actingAs($this->studentUser)->get(route('student.fees.index'))->assertOk()->assertSee('100.10')->assertSee(Payment::first()->receipt_number)->assertDontSee(Payment::orderByDesc('id')->first()->receipt_number);
        $this->actingAs($this->studentUser)->get(route('accounting.fees.show', $fee))->assertOk();
        $this->actingAs($this->studentUser)->get(route('accounting.fees.show', $otherFee))->assertForbidden();
        $this->actingAs($this->studentUser)->get(route('payments.receipt', Payment::first()))->assertOk()->assertSee('سند قبض');
        $this->actingAs($this->studentUser)->get(route('payments.receipt', Payment::orderByDesc('id')->first()))->assertForbidden();
        $this->actingAs($this->accountant)->get(route('payments.receipt', Payment::orderByDesc('id')->first()))->assertOk();
    }

    /** @test */
    public function the_accountant_lists_and_filters_fees_by_status()
    {
        $fee = $this->fee();
        [, $other] = $this->makeStudent();
        $paid = app(FeeService::class)->generateFor($other, $this->year, $this->accountant);
        $this->pay($paid, 100.10);

        $this->actingAs($this->accountant)->get(route('accounting.fees.index', ['status' => 'unsettled']))->assertOk()->assertSee($this->profile->number_student)->assertDontSee($other->number_student);
        $this->actingAs($this->accountant)->get(route('accounting.fees.index', ['status' => 'settled']))->assertOk()->assertSee($other->number_student)->assertDontSee($this->profile->number_student);
    }

    /** @test */
    public function the_migrations_roll_back_cleanly()
    {
        foreach (['2026_11_20_000002_create_payments_table', '2026_11_20_000001_create_student_fees_table'] as $name) {
            (require base_path("database/migrations/$name.php"))->down();
        }
        $this->assertFalse(\Schema::hasTable('payments'));
        $this->assertFalse(\Schema::hasTable('student_fees'));
        foreach (['2026_11_20_000001_create_student_fees_table', '2026_11_20_000002_create_payments_table'] as $name) {
            (require base_path("database/migrations/$name.php"))->up();
        }
        $this->assertTrue(\Schema::hasTable('payments'));
    }
}
