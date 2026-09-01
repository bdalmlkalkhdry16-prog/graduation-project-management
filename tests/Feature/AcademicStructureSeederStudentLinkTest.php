<?php

namespace Tests\Feature;

use App\Models\Specialization;
use App\Models\StudentProfile;
use App\Models\User;
use Database\Seeders\AcademicStructureSeeder;
use Database\Seeders\AcademicYearSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 3 — Academic Structure.
 * يتحقق أن AcademicStructureSeeder يربط ملفات الطلاب من Phase 2
 * ببرامجها المطابقة تلقائيًا، دون تخمين current_level_id.
 */
class AcademicStructureSeederStudentLinkTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_links_existing_phase2_student_profiles_to_their_matching_program()
    {
        $specialization = Specialization::factory()->create();
        $user = User::factory()->student()->create();

        // ملف طالب بأسلوب Phase 2 (بدون program_id) — يحاكي بيانات حقيقية سابقة
        $profile = StudentProfile::create([
            'user_id' => $user->id,
            'number_student' => '20260789',
            'specialization_id' => $specialization->id,
        ]);

        $this->seed(AcademicYearSeeder::class);
        $this->seed(AcademicStructureSeeder::class);

        $profile->refresh();

        $this->assertNotNull($profile->program_id);
        $this->assertEquals($specialization->id, $profile->program->specialization_id);
        // current_level_id لا يُخمَّن — يبقى فارغًا حتى تحديده يدويًا
        $this->assertNull($profile->current_level_id);
    }

    /** @test */
    public function it_does_not_overwrite_a_program_already_set_manually()
    {
        $specializationA = Specialization::factory()->create();
        $specializationB = Specialization::factory()->create();
        $user = User::factory()->student()->create();

        $this->seed(AcademicYearSeeder::class);
        $this->seed(AcademicStructureSeeder::class);

        $manualProgram = \App\Models\Program::where('specialization_id', $specializationB->id)->first();

        $profile = StudentProfile::create([
            'user_id' => $user->id,
            'number_student' => '20260999',
            'specialization_id' => $specializationA->id,
            'program_id' => $manualProgram->id,
        ]);

        $this->seed(AcademicStructureSeeder::class);

        $this->assertEquals($manualProgram->id, $profile->fresh()->program_id);
    }
}
