<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Specialization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        $role = $this->faker->randomElement(['student', 'supervisor', 'admin']);
        
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => $role,
            'student_id' => $role === 'student' ? $this->faker->unique()->numerify('########') : null,
            'employee_id' => $role === 'supervisor' ? $this->faker->unique()->numerify('EMP###') : null,
            'phone' => $this->faker->phoneNumber(),
            'profile_photo' => null,
            'specialization_id' => $role !== 'admin' ? Specialization::factory() : null,
            'is_active' => true,
            'last_login_at' => null,
            'remember_token' => null,
        ];
    }

    public function student(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'student',
            'student_id' => $this->faker->unique()->numerify('########'),
            'employee_id' => null,
            'specialization_id' => Specialization::factory(),
        ]);
    }

    public function supervisor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'supervisor',
            'student_id' => null,
            'employee_id' => $this->faker->unique()->numerify('EMP###'),
            'specialization_id' => Specialization::factory(),
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'student_id' => null,
            'employee_id' => null,
            'specialization_id' => null,
        ]);
    }
}