<?php

namespace Database\Factories;

use App\Models\Specialization;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpecializationFactory extends Factory
{
    protected $model = Specialization::class;

    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'name_ar' => $this->faker->unique()->word() . ' ' . $this->faker->word(),
            'name_en' => $this->faker->unique()->word(),
            'code' => strtoupper($this->faker->unique()->lexify('SPEC???')),
            'description' => $this->faker->sentence(6),
            'duration_years' => $this->faker->randomElement([2, 4]),
            'is_active' => true,
        ];
    }
}