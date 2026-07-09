<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\College;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'college_id' => College::factory(),
            'name_ar' => $this->faker->unique()->word(),
            'name_en' => $this->faker->unique()->word(),
            'code' => strtoupper($this->faker->unique()->lexify('DEPT???')),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}