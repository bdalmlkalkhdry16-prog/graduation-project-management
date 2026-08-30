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
            // Phase 1 — Roles & Permissions (إضافة جديدة، بعد UserSeeder لأنها تعتمد عليه)
            RolesAndPermissionsSeeder::class,
            LegacyUserRolesDataSeeder::class,
            // Phase 2 — Student/Faculty/Staff Central Profiles (تعتمد على Phase 1)
            LegacyStudentProfileDataSeeder::class,
            LegacyFacultyProfileDataSeeder::class,
        ]);
    }
}
