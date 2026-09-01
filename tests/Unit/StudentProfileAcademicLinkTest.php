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

    /** @test */
    public function it_rejects_a_current_level_that_belongs_to_a_different_program()
    {
        // إصلاح معماري بعد مراجعة Phase 3: يمنع تعارض program_id مع
        // current_level_id.program_id
        $specialization = Specialization::factory()->create();
        $programA = Program::create(['specialization_id' => $specialization->id, 'level' => 'bachelor']);
        $programB = Program::create(['specialization_id' => $specialization->id, 'level' => 'diploma']);
        $levelOfProgramB = Level::create(['program_id' => $programB->id, 'level_number' => 1]);

        $user = User::factory()->student()->create();

        $this->expectException(\InvalidArgumentException::class);

        StudentProfile::create([
            'user_id' => $user->id,
            'number_student' => '20260321',
            'program_id' => $programA->id,
            'current_level_id' => $levelOfProgramB->id, // ينتمي لبرنامج آخر
        ]);
    }

    /** @test */
    public function it_rejects_the_mismatch_on_update_too_not_only_on_create()
    {
        $specialization = Specialization::factory()->create();
        $programA = Program::create(['specialization_id' => $specialization->id, 'level' => 'bachelor']);
        $programB = Program::create(['specialization_id' => $specialization->id, 'level' => 'diploma']);
        $levelOfProgramA = Level::create(['program_id' => $programA->id, 'level_number' => 1]);
        $levelOfProgramB = Level::create(['program_id' => $programB->id, 'level_number' => 1]);

        $user = User::factory()->student()->create();
        $profile = StudentProfile::create([
            'user_id' => $user->id,
            'number_student' => '20260654',
            'program_id' => $programA->id,
            'current_level_id' => $levelOfProgramA->id,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        $profile->update(['current_level_id' => $levelOfProgramB->id]);
    }

    /** @test */
    public function it_allows_changing_program_and_level_together_consistently()
    {
        $specialization = Specialization::factory()->create();
        $programA = Program::create(['specialization_id' => $specialization->id, 'level' => 'bachelor']);
        $programB = Program::create(['specialization_id' => $specialization->id, 'level' => 'diploma']);
        $levelOfProgramA = Level::create(['program_id' => $programA->id, 'level_number' => 1]);
        $levelOfProgramB = Level::create(['program_id' => $programB->id, 'level_number' => 1]);

        $user = User::factory()->student()->create();
        $profile = StudentProfile::create([
            'user_id' => $user->id,
            'number_student' => '20260777',
            'program_id' => $programA->id,
            'current_level_id' => $levelOfProgramA->id,
        ]);

        // تحويل الطالب لبرنامج آخر + مستوى ذلك البرنامج معًا (متسق) → مسموح
        $profile->update([
            'program_id' => $programB->id,
            'current_level_id' => $levelOfProgramB->id,
        ]);

        $this->assertEquals($programB->id, $profile->fresh()->program_id);
        $this->assertEquals($levelOfProgramB->id, $profile->fresh()->current_level_id);
    }

    /** @test */
    public function it_rejects_a_specialization_that_does_not_match_the_programs_specialization()
    {
        // إصلاح معماري إضافي بعد الفحص الثاني: نفس فئة الخلل، لكن بين
        // specialization_id و program.specialization_id.
        $specializationA = Specialization::factory()->create();
        $specializationB = Specialization::factory()->create();
        $programOfA = Program::create(['specialization_id' => $specializationA->id, 'level' => 'bachelor']);

        $user = User::factory()->student()->create();

        $this->expectException(\InvalidArgumentException::class);

        StudentProfile::create([
            'user_id' => $user->id,
            'number_student' => '20260888',
            'specialization_id' => $specializationB->id, // لا يطابق تخصص البرنامج
            'program_id' => $programOfA->id,
        ]);
    }

    /** @test */
    public function it_allows_specialization_id_alone_before_a_program_is_assigned()
    {
        // القبول الأولي: specialization_id معروف، program_id لم يُحدَّد بعد → مسموح
        $specialization = Specialization::factory()->create();
        $user = User::factory()->student()->create();

        $profile = StudentProfile::create([
            'user_id' => $user->id,
            'number_student' => '20260999',
            'specialization_id' => $specialization->id,
        ]);

        $this->assertEquals($specialization->id, $profile->specialization_id);
        $this->assertNull($profile->program_id);
    }

    /** @test */
    public function it_allows_a_matching_specialization_and_program_together()
    {
        $specialization = Specialization::factory()->create();
        $program = Program::create(['specialization_id' => $specialization->id, 'level' => 'bachelor']);

        $user = User::factory()->student()->create();
        $profile = StudentProfile::create([
            'user_id' => $user->id,
            'number_student' => '20261000',
            'specialization_id' => $specialization->id,
            'program_id' => $program->id,
        ]);

        $this->assertEquals($specialization->id, $profile->fresh()->specialization_id);
        $this->assertEquals($program->id, $profile->fresh()->program_id);
    }
}
