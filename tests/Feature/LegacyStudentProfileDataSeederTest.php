<?php

namespace Tests\Feature;

use App\Models\Specialization;
use App\Models\StudentProfile;
use App\Models\User;
use Database\Seeders\LegacyStudentProfileDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyStudentProfileDataSeederTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_profile_for_each_legacy_student_with_a_student_id()
    {
        $specialization = Specialization::factory()->create();
        $student = User::factory()->student()->create([
            'student_id' => '20240077',
            'specialization_id' => $specialization->id,
        ]);

        $this->seed(LegacyStudentProfileDataSeeder::class);

        $this->assertDatabaseHas('student_profiles', [
            'user_id' => $student->id,
            'number_student' => '20240077',
            'specialization_id' => $specialization->id,
            'academic_status' => 'active',
        ]);
    }

    /** @test */
    public function it_skips_students_without_a_legacy_student_id_instead_of_guessing()
    {
        $student = User::factory()->student()->create(['student_id' => null]);

        $this->seed(LegacyStudentProfileDataSeeder::class);

        $this->assertNull($student->fresh()->studentProfile);
    }

    /** @test */
    public function running_the_seeder_twice_does_not_duplicate_profiles()
    {
        User::factory()->student()->create(['student_id' => '20240088']);

        $this->seed(LegacyStudentProfileDataSeeder::class);
        $this->seed(LegacyStudentProfileDataSeeder::class);

        $this->assertEquals(1, StudentProfile::where('number_student', '20240088')->count());
    }

    /** @test */
    public function it_does_not_touch_the_legacy_students_role_column()
    {
        $student = User::factory()->student()->create(['student_id' => '20240099']);

        $this->seed(LegacyStudentProfileDataSeeder::class);

        $this->assertEquals('student', $student->fresh()->role);
    }
}
