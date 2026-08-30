<?php

namespace Tests\Unit;

use App\Models\FacultyProfile;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacultyProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_a_user_and_a_specialization()
    {
        $user = User::factory()->supervisor()->create();
        $specialization = Specialization::factory()->create();

        $profile = FacultyProfile::create([
            'user_id' => $user->id,
            'specialization_id' => $specialization->id,
            'academic_rank' => 'أستاذ مساعد',
            'hiring_year' => 2020,
        ]);

        $this->assertTrue($user->facultyProfile->is($profile));
        $this->assertTrue($profile->specialization->is($specialization));
    }

    /** @test */
    public function a_supervisor_with_faculty_profile_can_hold_both_roles_at_once()
    {
        $user = User::factory()->supervisor()->create();

        $supervisorRole = \App\Models\Role::create(['name' => 'مشرف', 'slug' => 'supervisor', 'is_system' => true]);
        $facultyRole = \App\Models\Role::create(['name' => 'عضو هيئة تدريس', 'slug' => 'faculty', 'is_system' => true]);

        $user->userRoles()->create(['role_id' => $supervisorRole->id]);
        $user->userRoles()->create(['role_id' => $facultyRole->id]);

        FacultyProfile::create(['user_id' => $user->id]);

        $this->assertTrue($user->hasRole('supervisor'));
        $this->assertTrue($user->hasRole('faculty'));
        $this->assertNotNull($user->facultyProfile);
    }
}
