<?php

namespace Tests\Unit;

use App\Models\Level;
use App\Models\Program;
use App\Models\Specialization;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 3 — Academic Structure.
 * يغطي تحديدًا طلب "ربط الطالب بالتخصص والبرنامج والمستوى".
 */
class StudentProfileAcademicLinkTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_student_profile_can_be_linked_to_its_program_and_current_level()
    {
        $specialization = Specialization::factory()->create();
        $program = Program::create(['specialization_id' => $specialization->id, 'level' => 'bachelor']);
        $level = Level::create(['program_id' => $program->id, 'level_number' => 2, 'name' => 'المستوى الثاني']);

        $user = User::factory()->student()->create();
        $profile = StudentProfile::create([
            'user_id' => $user->id,
            'number_student' => '20260123',
            'specialization_id' => $specialization->id,
            'program_id' => $program->id,
            'current_level_id' => $level->id,
        ]);

        $this->assertTrue($profile->program->is($program));
        $this->assertTrue($profile->currentLevel->is($level));
        $this->assertTrue($program->studentProfiles->contains($profile));
        $this->assertTrue($level->studentProfiles->contains($profile));
    }

    /** @test */
    public function program_and_current_level_are_optional_and_do_not_break_phase2_profiles()
    {
        // ملف طالب بأسلوب Phase 2 القديم (بدون برنامج/مستوى منظَّم) يجب أن يبقى صالحًا
        $user = User::factory()->student()->create();
        $profile = StudentProfile::create([
            'user_id' => $user->id,
            'number_student' => '20260456',
        ]);

        $this->assertNull($profile->program);
        $this->assertNull($profile->currentLevel);
    }
}
