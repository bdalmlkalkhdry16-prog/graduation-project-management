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

class FeeDiscountTest extends TestCase
{
    use RefreshDatabase, AcademicFixtures;

    private $accountant;
    private $admin;
    private $studentUser;
    private $profile;
    private $fee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAcademic();
        $this->accountant = $this->userWithRole('accountant');
        $this->admin = $this->userWithRole('admin', 'admin');
        [$this->studentUser, $this->profile] = $this->makeStudent();
        FeeSchedule::create(['department_id' => $this->spec->department_id, 'academic_year_id' => $this->term->academicYear->id, 'amount' => 200.50, 'currency' => 'USD']);
        $this->fee = app(FeeService::class)->generateFor($this->profile, $this->term->academicYear, $this->accountant);
    }

    private function discount($type, $value, $reason = 'منحة دراسية', $user = null)
    {
        return $this->actingAs($user ?? $this->admin)->post(route('accounting.discounts.store', $this->fee), ['type' => $type, 'value' => $value, 'reason' => $reason]);
    }

    /** @test */
    public function an_amount_discount_reduces_what_is_owed_but_not_the_pinned_original_amount()
    {
        $this->discount('amount', 50.25)->assertSessionHasNoErrors();
        $fee = $this->fee->fresh();
        $this->assertSame(20050, \App\Models\StudentFee::toCents($fee->amount));
        $this->assertSame(5025, $fee->discountCents());
        $this->assertSame(15025, $fee->remainingCents());
        $this->assertTrue(ActivityLog::where('action', 'fee_discount.added')->exists());
    }

    /** @test */
    public function a_percent_discount_is_pinned_to_an_amount_and_rounded_to_the_cent()
    {
        $this->discount('percent', 25)->assertSessionHasNoErrors();
        $d = $this->fee->discounts()->first();
        $this->assertSame('50.13', (string) $d->amount); // 200.50 * 25% = 50.125 → 50.13
        $this->assertSame('25.00', (string) $d->value);

        // تغيّر سعر الجدول لاحقًا لا يغيّر الخصم المثبت
        FeeSchedule::first()->update(['amount' => 999]);
        $this->assertSame(5013, $this->fee->fresh()->discountCents());
    }

    /** @test */
    public function a_full_exemption_settles_the_fee_and_releases_the_results()
    {
        $this->discount('percent', 100)->assertSessionHasNoErrors();
        $this->assertTrue($this->fee->fresh()->isSettled());
        $this->assertTrue(app(FeeStatus::class)->withheldYears($this->profile)->isEmpty());
    }

    /** @test */
    public function discounts_cannot_exceed_the_remaining_or_go_below_what_was_paid_and_validate_input()
    {
        app(FeeService::class)->recordPayment($this->fee, 150, now()->toDateString(), null, $this->accountant);

        $this->discount('amount', 60)->assertSessionHasErrors('value'); // المتبقي 50.50
        $this->discount('percent', 101)->assertSessionHasErrors('value');
        $this->discount('amount', 0)->assertSessionHasErrors('value');
        $this->discount('amount', 10, 'abc')->assertSessionHasErrors('reason');
        $this->discount('weird', 10)->assertSessionHasErrors('type');
        $this->assertSame(0, $this->fee->discounts()->count());

        $this->discount('amount', 50.50)->assertSessionHasNoErrors();
        $this->assertTrue($this->fee->fresh()->isSettled());
        $this->discount('amount', 1)->assertSessionHasErrors('value');
    }

    /** @test */
    public function payments_are_capped_by_the_net_amount_after_discount()
    {
        $this->discount('amount', 100.50);
        $this->actingAs($this->accountant)->post(route('accounting.payments.store', $this->fee), ['amount' => 101, 'paid_at' => now()->toDateString()])->assertSessionHasErrors('amount');
        $this->actingAs($this->accountant)->post(route('accounting.payments.store', $this->fee), ['amount' => 100, 'paid_at' => now()->toDateString()])->assertSessionHasNoErrors();
        $this->assertTrue($this->fee->fresh()->isSettled());
    }

    /** @test */
    public function revoking_a_discount_needs_a_reason_restores_the_amount_and_cannot_repeat()
    {
        $this->discount('amount', 200.50);
        $d = $this->fee->discounts()->first();
        $this->assertTrue($this->fee->fresh()->isSettled());

        $this->actingAs($this->admin)->post(route('accounting.discounts.revoke', $d), [])->assertSessionHasErrors('reason');
        $this->actingAs($this->admin)->post(route('accounting.discounts.revoke', $d), ['reason' => 'منحة أُلغيت'])->assertSessionHasNoErrors();
        $this->assertSame(0, $this->fee->fresh()->discountCents());
        $this->assertSame(20050, $this->fee->fresh()->remainingCents());
        $this->assertCount(1, app(FeeStatus::class)->withheldYears($this->profile));
        $this->actingAs($this->admin)->post(route('accounting.discounts.revoke', $d), ['reason' => 'مرة ثانية'])->assertSessionHasErrors('discount');
        $this->assertTrue(ActivityLog::where('action', 'fee_discount.revoked')->exists());
    }

    /** @test */
    public function only_the_admin_or_a_role_with_the_permission_can_discount()
    {
        foreach ([$this->accountant, $this->studentUser, $this->userWithRole('control')] as $u) {
            $this->discount('amount', 10, 'سبب كافٍ', $u)->assertForbidden();
        }
        $this->assertSame(0, $this->fee->discounts()->count());

        $this->discount('amount', 10, 'سبب كافٍ', $this->userWithPermissions(['fees.discount']))->assertSessionHasNoErrors();
        $this->assertSame(1, $this->fee->discounts()->count());
    }

    /** @test */
    public function views_show_the_discount_to_the_student_and_the_form_only_to_the_admin()
    {
        $this->discount('amount', 10, 'خصم أشقاء');
        $this->actingAs($this->admin)->get(route('accounting.fees.show', $this->fee))->assertSee('إضافة خصم')->assertSee('خصم أشقاء');
        $this->actingAs($this->accountant)->get(route('accounting.fees.show', $this->fee))->assertSee('خصم أشقاء')->assertDontSee('إضافة خصم');
        $this->actingAs($this->studentUser)->get(route('student.fees.index'))->assertOk()->assertSee('الخصومات');
    }

    /** @test */
    public function the_discount_migration_rolls_back_cleanly()
    {
        $migration = require database_path('migrations/2026_11_22_000001_create_fee_discounts_table.php');
        $migration->down();
        $this->assertFalse(Schema::hasTable('fee_discounts'));
        $migration->up();
        $this->assertTrue(Schema::hasTable('fee_discounts'));
    }
}
