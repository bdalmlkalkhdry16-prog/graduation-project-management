<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\User;
use App\Models\Specialization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_project()
    {
        $supervisor = User::factory()->supervisor()->create();
        $specialization = Specialization::factory()->create();

        $project = Project::create([
            'title_ar' => 'نظام اختبار',
            'abstract_ar' => 'ملخص المشروع',
            'supervisor_id' => $supervisor->id,
            'specialization_id' => $specialization->id,
            'academic_year' => 2025,
            'semester' => 'first',
            'status' => Project::STATUS_DRAFT,
        ]);

        $this->assertDatabaseHas('projects', ['title_ar' => 'نظام اختبار']);
        $this->assertEquals('مسودة', $project->status_name);
    }

    /** @test */
    public function it_calculates_success_percentage_correctly()
    {
        $project = Project::factory()->create();
        $evaluation = $project->evaluations()->create([
            'supervisor_id' => User::factory()->supervisor()->create()->id,
            'creativity_score' => 80,
            'implementation_score' => 70,
            'documentation_score' => 90,
            'presentation_score' => 85,
            'status' => 'finalized',
        ]);

        $percentage = $project->calculateSuccessPercentage();
        // (80*0.4)+(70*0.3)+(90*0.2)+(85*0.1) = 32+21+18+8.5 = 79.5
        $this->assertEquals(79.5, $percentage);
    }
}