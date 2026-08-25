<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserRole;
use Database\Seeders\LegacyUserRolesDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyUserRolesDataSeederTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function each_legacy_user_gets_a_matching_user_role()
    {
        $admin = User::factory()->admin()->create();
        $supervisor = User::factory()->supervisor()->create();
        $student = User::factory()->student()->create();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(LegacyUserRolesDataSeeder::class);

        $this->assertTrue($admin->fresh()->hasRole('admin'));
        $this->assertTrue($supervisor->fresh()->hasRole('supervisor'));
        $this->assertTrue($student->fresh()->hasRole('student'));

        // النطاق department_id يجب أن يكون null (الربط الحالي بالتخصص لا بالقسم)
        $this->assertDatabaseHas('user_roles', [
            'user_id' => $admin->id,
            'department_id' => null,
        ]);
    }

    /** @test */
    public function running_the_seeder_twice_does_not_duplicate_assignments()
    {
        $student = User::factory()->student()->create();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(LegacyUserRolesDataSeeder::class);
        $this->seed(LegacyUserRolesDataSeeder::class);

        $this->assertEquals(
            1,
            UserRole::where('user_id', $student->id)->count()
        );
    }

    /** @test */
    public function it_does_not_modify_the_legacy_role_column()
    {
        $supervisor = User::factory()->supervisor()->create();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(LegacyUserRolesDataSeeder::class);

        // عمود role القديم يجب أن يبقى كما هو تمامًا
        $this->assertEquals('supervisor', $supervisor->fresh()->role);
    }
}
