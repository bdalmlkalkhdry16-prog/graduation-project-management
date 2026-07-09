<?php

namespace Database\Factories;

use App\Models\College;
use Illuminate\Database\Eloquent\Factories\Factory;

class CollegeFactory extends Factory
{
    protected $model = College::class;

    public function definition(): array
    {
        return [
            'name_ar' => $this->faker->unique()->company(),
            'name_en' => $this->faker->unique()->company(),
            'code' => strtoupper($this->faker->unique()->lexify('COL???')),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}