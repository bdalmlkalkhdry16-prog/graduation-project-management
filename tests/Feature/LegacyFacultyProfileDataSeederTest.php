<?php

namespace Tests\Feature;

use App\Models\FacultyProfile;
use App\Models\User;
use Database\Seeders\LegacyFacultyProfileDataSeeder;
use Database\Seeders\LegacyUserRolesDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyFacultyProfileDataSeederTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function every_legacy_supervisor_gets_a_faculty_profile_and_the_faculty_role()
    {
        $supervisor = User::factory()->supervisor()->create();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(LegacyUserRolesDataSeeder::class);
        $this->seed(LegacyFacultyProfileDataSeeder::class);

        $this->assertNotNull($supervisor->fresh()->facultyProfile);
        $this->assertTrue($supervisor->fresh()->hasRole('faculty'));
        // الدور القديم supervisor يبقى محتفظًا به بجانب faculty الجديد
        $this->assertTrue($supervisor->fresh()->hasRole('supervisor'));
    }

    /** @test */
    public function running_the_seeder_twice_does_not_duplicate_profile_or_role()
    {
        $supervisor = User::factory()->supervisor()->create();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(LegacyUserRolesDataSeeder::class);
        $this->seed(LegacyFacultyProfileDataSeeder::class);
        $this->seed(LegacyFacultyProfileDataSeeder::class);

        $this->assertEquals(1, FacultyProfile::where('user_id', $supervisor->id)->count());
        $this->assertEquals(
            1,
            $supervisor->userRoles()->whereHas('role', fn ($q) => $q->where('slug', 'faculty'))->count()
        );
    }

    /** @test */
    public function students_do_not_get_a_faculty_profile()
    {
        $student = User::factory()->student()->create();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(LegacyUserRolesDataSeeder::class);
        $this->seed(LegacyFacultyProfileDataSeeder::class);

        $this->assertNull($student->fresh()->facultyProfile);
        $this->assertFalse($student->fresh()->hasRole('faculty'));
    }
}
