<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Specialization;
use App\Models\Project;
use App\Models\Idea;
use App\Models\Evaluation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MainFlowTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function student_can_submit_idea_and_supervisor_approves_it()
    {
        $student = User::factory()->student()->create();
        $specialization = Specialization::factory()->create();

        $response = $this->actingAs($student)
            ->post(route('projects.submit_idea'), [
                'title_ar' => 'فكرة مبتكرة',
                'abstract_ar' => 'ملخص الفكرة',
                'specialization_id' => $specialization->id,
            ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('ideas', ['title_ar' => 'فكرة مبتكرة', 'status' => 'pending']);

        $supervisor = User::factory()->supervisor()->create();
        $idea = Idea::where('title_ar', 'فكرة مبتكرة')->first();

        $response = $this->actingAs($supervisor)
            ->post(route('projects.idea.review', $idea), [
                'action' => 'approve',
                'review_notes' => 'فكرة ممتازة',
            ]);

        $response->assertRedirect();
        $idea->refresh();
        $this->assertDatabaseHas('ideas', ['id' => $idea->id, 'status' => 'approved']);
        $this->assertNotNull($idea->project_id, 'لم يتم إنشاء مشروع من الفكرة');
        $this->assertDatabaseHas('projects', ['id' => $idea->project_id]);
    }

    #[Test]
    public function student_can_create_project_and_submit_for_review()
    {
        $student = User::factory()->student()->create();
        $supervisor = User::factory()->supervisor()->create();
        $specialization = Specialization::factory()->create();

        $response = $this->actingAs($student)
            ->post(route('projects.store'), [
                'title_ar' => 'مشروع اختبار',
                'abstract_ar' => 'ملخص المشروع',
                'supervisor_id' => $supervisor->id,
                'specialization_id' => $specialization->id,
                'academic_year' => 2025,
                'semester' => 'first',
            ]);

        $response->assertRedirect();
        $project = Project::where('title_ar', 'مشروع اختبار')->first();
        $this->assertNotNull($project);
        $this->assertEquals('draft', $project->status);

        $project->students()->attach($student->id, ['role' => 'leader']);

        $response = $this->actingAs($student)
            ->post(route('projects.submit', $project));

        $response->assertRedirect();
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'status' => 'submitted']);
    }

    #[Test]
    public function supervisor_can_evaluate_project_and_student_sees_result()
    {
        $supervisor = User::factory()->supervisor()->create();
        $student = User::factory()->student()->create();
        $specialization = Specialization::factory()->create();

        $project = Project::create([
            'title_ar' => 'مشروع للتقييم',
            'abstract_ar' => 'ملخص المشروع',
            'supervisor_id' => $supervisor->id,
            'specialization_id' => $specialization->id,
            'academic_year' => 2025,
            'semester' => 'first',
            'status' => Project::STATUS_SUBMITTED,
            'submission_date' => now(),
        ]);
        $project->students()->attach($student->id, ['role' => 'member']);

        $creativity = 85;
        $implementation = 80;
        $documentation = 75;
        $presentation = 90;

        $response = $this->actingAs($supervisor)
            ->post(route('evaluations.store', $project), [
                'creativity_score' => $creativity,
                'implementation_score' => $implementation,
                'documentation_score' => $documentation,
                'presentation_score' => $presentation,
                'strengths' => 'ممتاز',
                'weaknesses' => 'لا يوجد',
                'status' => 'submitted',
            ]);

        $response->assertRedirect();
        $evaluation = Evaluation::where('project_id', $project->id)->first();
        $this->assertNotNull($evaluation, 'لم يتم إنشاء التقييم');

        // حساب النسبة المتوقعة بنفس طريقة النموذج (40%, 30%, 20%, 10%)
        $expected = round(($creativity * 0.40) + ($implementation * 0.30) + ($documentation * 0.20) + ($presentation * 0.10), 2);
        $this->assertEquals($expected, $evaluation->total_percentage);

        $response = $this->actingAs($student)
            ->get(route('evaluations.show', $evaluation));

        $response->assertOk();
        $response->assertSee((string)$expected);
    }
}