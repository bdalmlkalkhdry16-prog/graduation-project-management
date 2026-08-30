<?php

namespace Tests\Unit;

use App\Models\Specialization;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_a_user_and_a_specialization()
    {
        $user = User::factory()->student()->create();
        $specialization = Specialization::factory()->create();

        $profile = StudentProfile::create([
            'user_id' => $user->id,
            'number_student' => '20260001',
            'specialization_id' => $specialization->id,
            'program_level' => 'bachelor',
            'level' => 3,
            'admission_year' => 2026,
        ]);

        $this->assertTrue($user->studentProfile->is($profile));
        $this->assertTrue($profile->specialization->is($specialization));
        $this->assertEquals('active', $profile->fresh()->academic_status); // default من قاعدة البيانات
    }

    /** @test */
    public function number_student_must_be_unique()
    {
        $userA = User::factory()->student()->create();
        $userB = User::factory()->student()->create();

        StudentProfile::create(['user_id' => $userA->id, 'number_student' => '20260099']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        StudentProfile::create(['user_id' => $userB->id, 'number_student' => '20260099']);
    }

    /** @test */
    public function legacy_users_student_id_column_is_untouched_when_profile_is_created()
    {
        $user = User::factory()->student()->create(['student_id' => '20240055']);

        StudentProfile::create([
            'user_id' => $user->id,
            'number_student' => 'NEW-2026-0001',
        ]);

        // العمود القديم يبقى كما هو تمامًا، حتى لو اختلف عن رقم القيد الجديد
        $this->assertEquals('20240055', $user->fresh()->student_id);
    }
}
