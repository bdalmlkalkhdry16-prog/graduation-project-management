<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Idea;
use App\Models\Specialization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdeaTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function student_can_submit_an_idea()
    {
        $student = User::factory()->student()->create();
        $specialization = Specialization::factory()->create();

        $response = $this->actingAs($student)
            ->post(route('projects.submit_idea'), [
                'title_ar' => 'فكرة رائعة',
                'abstract_ar' => 'هذه فكرة جديدة',
                'keywords' => 'تكنولوجيا, ذكاء اصطناعي',
                'specialization_id' => $specialization->id,
            ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ideas', [
            'title_ar' => 'فكرة رائعة',
            'student_id' => $student->id,
            'status' => Idea::STATUS_PENDING,
        ]);
    }

    /** @test */
    public function supervisor_can_approve_an_idea()
    {
        $supervisor = User::factory()->supervisor()->create();
        $student = User::factory()->student()->create();
        $idea = Idea::factory()->create([
            'student_id' => $student->id,
            'status' => Idea::STATUS_PENDING,
        ]);

        $response = $this->actingAs($supervisor)
            ->post(route('projects.idea.review', $idea), [
                'action' => 'approve',
                'review_notes' => 'فكرة ممتازة',
            ]);

        $response->assertRedirect(route('projects.idea.show', $idea));
        $this->assertDatabaseHas('ideas', [
            'id' => $idea->id,
            'status' => Idea::STATUS_APPROVED,
            'review_notes' => 'فكرة ممتازة',
        ]);
        // يجب أن ينشأ مشروع مرتبط
        $this->assertNotNull($idea->fresh()->project_id);
    }

    /** @test */
    public function idea_duplicate_check_works()
    {
        $student1 = User::factory()->student()->create();
        $student2 = User::factory()->student()->create();
        $spec = Specialization::factory()->create();

        // فكرة معتمدة سابقاً
        Idea::factory()->create([
            'title_ar' => 'نظام ذكي',
            'status' => Idea::STATUS_APPROVED,
        ]);

        $response = $this->actingAs($student2)
            ->post(route('projects.submit_idea'), [
                'title_ar' => 'نظام ذكي',
                'specialization_id' => $spec->id,
            ]);

        $response->assertSessionHas('error', 'يوجد فكرة معتمدة سابقة بنفس العنوان أو الكلمات المفتاحية.');
    }
}