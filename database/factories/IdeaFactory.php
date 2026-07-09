<?php

namespace Database\Factories;

use App\Models\Idea;
use App\Models\User;
use App\Models\Specialization;
use Illuminate\Database\Eloquent\Factories\Factory;

class IdeaFactory extends Factory
{
    protected $model = Idea::class;

    public function definition(): array
    {
        $statuses = [Idea::STATUS_PENDING, Idea::STATUS_APPROVED, Idea::STATUS_REJECTED];
        
        return [
            'title_ar' => $this->faker->sentence(4),
            'title_en' => $this->faker->sentence(4),
            'abstract_ar' => $this->faker->paragraph(2),
            'abstract_en' => $this->faker->paragraph(2),
            'keywords' => $this->faker->words(3, true),
            'student_id' => User::factory()->student(),
            'specialization_id' => Specialization::factory(),
            'status' => $this->faker->randomElement($statuses),
            'review_notes' => $this->faker->optional()->sentence(),
            'reviewed_by' => null,
            'submitted_at' => now(),
            'reviewed_at' => null,
            'project_id' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Idea::STATUS_APPROVED,
            'review_notes' => $this->faker->sentence(),
            'reviewed_by' => User::factory()->supervisor(),
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Idea::STATUS_REJECTED,
            'review_notes' => $this->faker->sentence(),
            'reviewed_by' => User::factory()->supervisor(),
            'reviewed_at' => now(),
        ]);
    }
}