<?php

namespace Tests\Unit;

use App\Models\Level;
use App\Models\Program;
use App\Models\Specialization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramAndLevelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_program_belongs_to_a_specialization_without_altering_it()
    {
        $specialization = Specialization::factory()->create(['duration_years' => 4]);

        $program = Program::create([
            'specialization_id' => $specialization->id,
            'level' => 'bachelor',
            'total_credit_hours' => 132,
        ]);

        $this->assertTrue($program->specialization->is($specialization));
        // duration_years يبقى مصدر الحقيقة على specializations، لم يُمس
        $this->assertEquals(4, $specialization->fresh()->duration_years);
    }

    /** @test */
    public function a_specialization_can_now_have_two_programs_at_different_levels()
    {
        // إصلاح معماري بعد مراجعة Phase 3: نفس التخصص يمكن أن يُقدَّم
        // كدبلوم وبكالوريوس معًا.
        $specialization = Specialization::factory()->create();

        $diploma = Program::create(['specialization_id' => $specialization->id, 'level' => 'diploma']);
        $bachelor = Program::create(['specialization_id' => $specialization->id, 'level' => 'bachelor']);

        $this->assertEquals(2, Program::where('specialization_id', $specialization->id)->count());
        $this->assertNotEquals($diploma->id, $bachelor->id);
    }

    /** @test */
    public function the_same_specialization_and_level_combination_cannot_repeat()
    {
        $specialization = Specialization::factory()->create();
        Program::create(['specialization_id' => $specialization->id, 'level' => 'diploma']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        // نفس التخصص + نفس المستوى مرة ثانية → يجب أن يُرفَض
        Program::create(['specialization_id' => $specialization->id, 'level' => 'diploma']);
    }

    /** @test */
    public function a_program_has_many_levels_with_unique_level_numbers()
    {
        $specialization = Specialization::factory()->create();
        $program = Program::create(['specialization_id' => $specialization->id, 'level' => 'bachelor']);

        Level::create(['program_id' => $program->id, 'level_number' => 1, 'name' => 'المستوى الأول']);
        Level::create(['program_id' => $program->id, 'level_number' => 2, 'name' => 'المستوى الثاني']);

        $this->assertEquals(2, $program->levels()->count());

        $this->expectException(\Illuminate\Database\QueryException::class);
        Level::create(['program_id' => $program->id, 'level_number' => 1, 'name' => 'تكرار']);
    }
}
