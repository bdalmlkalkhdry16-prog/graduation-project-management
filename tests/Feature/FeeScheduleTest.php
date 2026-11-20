<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\FeeSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AcademicFixtures;
use Tests\TestCase;

/**
 * F1a — الرسوم الدراسية: سعر لكل قسم عن كل سنة دراسية، بالريال اليمني القديم أو الدولار.
 */
class FeeScheduleTest extends TestCase
{
    use RefreshDatabase, AcademicFixtures;

    private $manager;
    private Department $dept;
    private AcademicYear $year;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAcademic();
        $this->manager = $this->userWithPermissions(['fees.manage']);
        $this->dept = $this->spec->department;
        $this->year = $this->term->academicYear;
    }

    private function payload(array $over = []): array
    {
        return array_merge(['department_id' => $this->dept->id, 'academic_year_id' => $this->year->id, 'amount' => 150000, 'currency' => 'YER'], $over);
    }

    /** @test */
    public function the_manager_creates_a_price_per_department_and_year_in_either_currency()
    {
        $this->actingAs($this->manager)->post(route('fee-schedules.store'), $this->payload())->assertSessionHasNoErrors()->assertRedirect(route('fee-schedules.index'));
        $this->assertDatabaseHas('fee_schedules', ['department_id' => $this->dept->id, 'academic_year_id' => $this->year->id, 'amount' => 150000, 'currency' => 'YER', 'updated_by' => $this->manager->id]);

        $other = Department::factory()->create();
        $this->actingAs($this->manager)->post(route('fee-schedules.store'), $this->payload(['department_id' => $other->id, 'amount' => 500.50, 'currency' => 'USD']))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('fee_schedules', ['department_id' => $other->id, 'amount' => 500.50, 'currency' => 'USD']);
    }

    /** @test */
    public function each_department_keeps_its_own_independent_price()
    {
        $other = Department::factory()->create();
        $this->actingAs($this->manager)->post(route('fee-schedules.store'), $this->payload());
        $this->actingAs($this->manager)->post(route('fee-schedules.store'), $this->payload(['department_id' => $other->id, 'amount' => 800, 'currency' => 'USD']));

        $first = FeeSchedule::where('department_id', $this->dept->id)->firstOrFail();
        $this->actingAs($this->manager)->put(route('fee-schedules.update', $first), $this->payload(['amount' => 175000]))->assertSessionHasNoErrors();

        $this->assertEquals(175000, $first->fresh()->amount);
        $this->assertEquals(800, FeeSchedule::where('department_id', $other->id)->firstOrFail()->amount);
        $this->assertSame('USD', FeeSchedule::where('department_id', $other->id)->firstOrFail()->currency);
    }

    /** @test */
    public function validation_rejects_bad_amounts_currencies_and_duplicates()
    {
        foreach ([['amount' => 0], ['amount' => -5], ['amount' => 'abc'], ['amount' => 10.555], ['currency' => 'SAR'], ['currency' => ''], ['department_id' => 9999], ['academic_year_id' => 9999]] as $bad) {
            $this->actingAs($this->manager)->post(route('fee-schedules.store'), $this->payload($bad))->assertSessionHasErrors(array_key_first($bad));
        }
        $this->assertSame(0, FeeSchedule::count());

        $this->actingAs($this->manager)->post(route('fee-schedules.store'), $this->payload())->assertSessionHasNoErrors();
        $this->actingAs($this->manager)->post(route('fee-schedules.store'), $this->payload(['amount' => 1]))->assertSessionHasErrors('academic_year_id');
        $this->assertSame(1, FeeSchedule::count());
    }

    /** @test */
    public function the_price_can_be_deleted_by_the_manager()
    {
        $s = FeeSchedule::create($this->payload());

        $this->actingAs($this->manager)->delete(route('fee-schedules.destroy', $s))->assertRedirect();
        $this->assertDatabaseMissing('fee_schedules', ['id' => $s->id]);
    }

    /** @test */
    public function viewing_needs_fees_view_and_changing_needs_fees_manage()
    {
        $s = FeeSchedule::create($this->payload());
        $viewer = $this->userWithPermissions(['fees.view']);

        $this->actingAs($viewer)->get(route('fee-schedules.index'))->assertOk()->assertSee('150,000.00');
        $this->actingAs($viewer)->get(route('fee-schedules.create'))->assertForbidden();
        $this->actingAs($viewer)->post(route('fee-schedules.store'), $this->payload(['amount' => 1]))->assertForbidden();
        $this->actingAs($viewer)->put(route('fee-schedules.update', $s), $this->payload(['amount' => 1]))->assertForbidden();
        $this->actingAs($viewer)->delete(route('fee-schedules.destroy', $s))->assertForbidden();
        $this->assertEquals(150000, $s->fresh()->amount);
    }

    /** @test */
    public function students_faculty_and_guests_cannot_see_or_change_prices()
    {
        $s = FeeSchedule::create($this->payload());
        [$studentUser] = $this->makeStudent();

        foreach ([$studentUser, $this->facultyUser, $this->userWithRole('eservices_officer'), $this->userWithRole('staff')] as $user) {
            $this->actingAs($user)->get(route('fee-schedules.index'))->assertForbidden();
            $this->actingAs($user)->delete(route('fee-schedules.destroy', $s))->assertForbidden();
        }
        auth()->logout();
        $this->get(route('fee-schedules.index'))->assertRedirect();
        $this->assertDatabaseHas('fee_schedules', ['id' => $s->id]);
    }

    /** @test */
    public function the_admin_manages_prices_and_no_role_holds_the_fee_permissions_by_default()
    {
        $this->actingAs($this->userWithRole('admin'))->get(route('fee-schedules.index'))->assertOk();
        $holders = \DB::table('role_permissions')
            ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
            ->join('roles', 'roles.id', '=', 'role_permissions.role_id')
            ->where('roles.is_system', true)->whereIn('permissions.slug', ['fees.manage', 'fees.view'])->pluck('roles.slug')->unique()->all();
        $this->assertSame([], array_values(array_diff($holders, ['admin'])));
    }

    /** @test */
    public function the_migration_rolls_back_cleanly()
    {
        $migration = require base_path('database/migrations/2026_11_20_000000_create_fee_schedules_table.php');
        $migration->down();
        $this->assertFalse(\Schema::hasTable('fee_schedules'));
        $migration->up();
        $this->assertTrue(\Schema::hasTable('fee_schedules'));
    }
}
