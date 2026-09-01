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
        $specializationB = Specialization::factory()->create();
        $user = User::factory()->student()->create();

        $this->seed(AcademicYearSeeder::class);
        $this->seed(AcademicStructureSeeder::class);

        $manualProgram = \App\Models\Program::where('specialization_id', $specializationB->id)->first();

        // specialization_id يطابق البرنامج المُسنَد يدويًا (بيانات متسقة،
        // يفرضها الآن Model نفسه) — الاختبار يتحقق تحديدًا أن program_id
        // المُسنَد يدويًا لا يُستبدَل، وليس تحديد specialization_id نفسه
        $profile = StudentProfile::create([
            'user_id' => $user->id,
            'number_student' => '20260999',
            'specialization_id' => $specializationB->id,
            'program_id' => $manualProgram->id,
        ]);

        $this->seed(AcademicStructureSeeder::class);

        $this->assertEquals($manualProgram->id, $profile->fresh()->program_id);
    }

    /** @test */
    public function it_leaves_program_id_empty_when_a_specialization_has_more_than_one_program()
    {
        // إصلاح معماري بعد مراجعة Phase 3: لا يخمّن أي برنامج عند وجود
        // أكثر من واحد لنفس التخصص (مثال: دبلوم وبكالوريوس معًا).
        $specialization = Specialization::factory()->create();
        \App\Models\Program::create(['specialization_id' => $specialization->id, 'level' => 'diploma']);
        \App\Models\Program::create(['specialization_id' => $specialization->id, 'level' => 'bachelor']);

        $user = User::factory()->student()->create();
        $profile = StudentProfile::create([
            'user_id' => $user->id,
            'number_student' => '20261111',
            'specialization_id' => $specialization->id,
        ]);

        $this->seed(AcademicYearSeeder::class);
        $this->seed(AcademicStructureSeeder::class);

        $this->assertNull($profile->fresh()->program_id);
    }

    /** @test */
    public function it_still_auto_links_when_the_specialization_has_exactly_one_program()
    {
        $specialization = Specialization::factory()->create();
        $program = \App\Models\Program::create(['specialization_id' => $specialization->id, 'level' => 'bachelor']);

        $user = User::factory()->student()->create();
        $profile = StudentProfile::create([
            'user_id' => $user->id,
            'number_student' => '20261222',
            'specialization_id' => $specialization->id,
        ]);

        $this->seed(AcademicYearSeeder::class);
        $this->seed(AcademicStructureSeeder::class);

        $this->assertEquals($program->id, $profile->fresh()->program_id);
    }
}
