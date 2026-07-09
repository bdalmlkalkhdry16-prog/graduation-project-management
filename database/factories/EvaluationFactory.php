<?php

namespace Database\Factories;

use App\Models\Evaluation;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EvaluationFactory extends Factory
{
    protected $model = Evaluation::class;

    public function definition(): array
    {
        $creativity = $this->faker->numberBetween(50, 100);
        $implementation = $this->faker->numberBetween(50, 100);
        $documentation = $this->faker->numberBetween(50, 100);
        $presentation = $this->faker->numberBetween(50, 100);
        $total = round($creativity * 0.4 + $implementation * 0.3 + $documentation * 0.2 + $presentation * 0.1, 2);
        
        return [
            'project_id' => Project::factory(),
            'supervisor_id' => User::factory()->supervisor(),
            'creativity_score' => $creativity,
            'implementation_score' => $implementation,
            'documentation_score' => $documentation,
            'presentation_score' => $presentation,
            'total_percentage' => $total,
            'strengths' => $this->faker->optional()->paragraph(),
            'weaknesses' => $this->faker->optional()->paragraph(),
            'recommendations' => $this->faker->optional()->paragraph(),
            'status' => $this->faker->randomElement(['draft', 'submitted', 'finalized']),
            'evaluated_at' => null,
        ];
    }

    public function finalized(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'finalized',
            'evaluated_at' => now(),
        ]);
    }
}