<?php

namespace Database\Seeders;

use App\Models\FacultyProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Phase 2 — Faculty Profile.
 *
 * لكل مستخدم قديم role=supervisor: يُنشأ له FacultyProfile (المشرف في
 * نظام مشاريع التخرج القديم هو نفسه عضو هيئة التدريس)، ويُمنح أيضًا
 * الدور الجديد "faculty" بجانب دوره الحالي "supervisor" من Phase 1
 * (لا يُستبدَل، الشخص يحمل أكثر من Role).
 *
 * يفترض تشغيل RolesAndPermissionsSeeder و LegacyUserRolesDataSeeder قبله.
 * Idempotent بالكامل.
 */
class LegacyFacultyProfileDataSeeder extends Seeder
{
    public function run(): void
    {
        $facultyRole = Role::where('slug', 'faculty')->first();

        User::query()
            ->where('role', 'supervisor')
            ->chunkById(200, function ($supervisors) use ($facultyRole) {
                foreach ($supervisors as $supervisor) {
                    if (! $supervisor->facultyProfile) {
                        FacultyProfile::create([
                            'user_id' => $supervisor->id,
                            'specialization_id' => $supervisor->specialization_id,
                        ]);
                    }

                    if ($facultyRole && ! $supervisor->hasRole('faculty')) {
                        $supervisor->userRoles()->create([
                            'role_id' => $facultyRole->id,
                            'department_id' => null,
                            'assigned_at' => now(),
                            'assigned_by' => null,
                        ]);
                    }
                }
            });
    }
}
