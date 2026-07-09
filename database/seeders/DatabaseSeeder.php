<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CollegeSeeder::class,
            DepartmentSeeder::class,
            SpecializationSeeder::class,
            UserSeeder::class,
            ProjectSeeder::class,
            EvaluationSeeder::class,
        ]);
    }
}
