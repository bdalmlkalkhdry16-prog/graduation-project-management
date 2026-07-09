<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use App\Models\Specialization;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $statuses = [
            Project::STATUS_DRAFT,
            Project::STATUS_SUBMITTED,
            Project::STATUS_UNDER_REVIEW,
            Project::STATUS_APPROVED,
            Project::STATUS_REJECTED,
            Project::STATUS_COMPLETED,
        ];
        $status = $this->faker->randomElement($statuses);
        
        return [
            'title_ar' => $this->faker->sentence(3),
            'title_en' => $this->faker->sentence(3),
            'abstract_ar' => $this->faker->paragraph(3),
            'abstract_en' => $this->faker->paragraph(3),
            'keywords' => $this->faker->words(5, true),
            'supervisor_id' => User::factory()->supervisor(),
            'specialization_id' => Specialization::factory(),
            'status' => $status,
            'academic_year' => $this->faker->numberBetween(2020, 2025),
            'semester' => $this->faker->randomElement(['first', 'second', 'summer']),
            'success_percentage' => $status === Project::STATUS_COMPLETED ? $this->faker->numberBetween(60, 100) : null,
            'feedback' => $status === Project::STATUS_REJECTED ? $this->faker->sentence(5) : null,
            'submission_date' => $status !== Project::STATUS_DRAFT ? $this->faker->dateTimeBetween('-6 months', 'now') : null,
            'approval_date' => in_array($status, [Project::STATUS_APPROVED, Project::STATUS_COMPLETED]) ? $this->faker->dateTimeBetween('-3 months', 'now') : null,
            'defense_date' => $status === Project::STATUS_COMPLETED ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
            // إضافة الحقول الجديدة إذا كانت موجودة
            'idea_approved' => false,
            'idea_submitted_at' => null,
            'idea_review_notes' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Project::STATUS_COMPLETED,
            'success_percentage' => $this->faker->numberBetween(60, 100),
            'approval_date' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'defense_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

   public function submitted(): static
{
    return $this->state(fn (array $attributes) => [
        'status' => Project::STATUS_SUBMITTED,
        'submission_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
    ]);
}
}