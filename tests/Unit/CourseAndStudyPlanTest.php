<?php

namespace Tests\Unit;

use App\Models\Course;
use App\Models\Level;
use App\Models\Program;
use App\Models\Specialization;
use App\Models\StudyPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseAndStudyPlanTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_course_belongs_to_a_specialization()
    {
        $specialization = Specialization::factory()->create();

        $course = Course::create([
            'specialization_id' => $specialization->id,
            'code' => 'CS101',
            'name_ar' => 'مقدمة في البرمجة',
            'credit_hours' => 3,
        ]);

        $this->assertTrue($course->specialization->is($specialization));
    }

    /** @test */
    public function a_study_plan_links_a_course_to_a_level_and_defaults_to_mandatory()
    {
        $specialization = Specialization::factory()->create();
        $program = Program::create(['specialization_id' => $specialization->id, 'level' => 'bachelor']);
        $level = Level::create(['program_id' => $program->id, 'level_number' => 1]);
        $course = Course::create([
            'specialization_id' => $specialization->id,
            'code' => 'CS101',
            'name_ar' => 'مقدمة في البرمجة',
            'credit_hours' => 3,
        ]);

        $plan = StudyPlan::create(['level_id' => $level->id, 'course_id' => $course->id]);

        $this->assertTrue($plan->fresh()->is_mandatory);
        $this->assertTrue($level->studyPlans->contains($plan));
        $this->assertTrue($course->studyPlans->contains($plan));
    }

    /** @test */
    public function the_same_course_cannot_be_added_twice_to_the_same_level()
    {
        $specialization = Specialization::factory()->create();
        $program = Program::create(['specialization_id' => $specialization->id, 'level' => 'bachelor']);
        $level = Level::create(['program_id' => $program->id, 'level_number' => 1]);
        $course = Course::create([
            'specialization_id' => $specialization->id,
            'code' => 'CS101',
            'name_ar' => 'مقدمة في البرمجة',
            'credit_hours' => 3,
        ]);

        StudyPlan::create(['level_id' => $level->id, 'course_id' => $course->id]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        StudyPlan::create(['level_id' => $level->id, 'course_id' => $course->id]);
    }
}
