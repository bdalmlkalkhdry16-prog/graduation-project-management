<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Phase 1 — Roles & Permissions.
 *
 * سكربت بيانات (Data Migration) وليس بنية: يمر على كل مستخدم موجود،
 * يقرأ عمود users.role القديم كما هو (بدون تعديله)، وينشئ له تعيين
 * مطابق في user_roles. department_id = null لأن الربط الحالي
 * بالتخصص (specialization) وليس بالقسم (department).
 *
 * Idempotent: يتحقق من عدم وجود التعيين مسبقًا قبل إدخاله، فيمكن
 * تشغيله أكثر من مرة بأمان دون تكرار.
 */
class LegacyUserRolesDataSeeder extends Seeder
{
    public function run(): void
    {
        // يفترض تشغيل RolesAndPermissionsSeeder قبله
        $roles = Role::whereIn('slug', ['admin', 'supervisor', 'student'])
            ->get()
            ->keyBy('slug');

        User::query()->chunkById(200, function ($users) use ($roles) {
            foreach ($users as $user) {
                $role = $roles->get($user->role);

                if (! $role) {
                    // قيمة role غير متوقعة (خارج admin/supervisor/student) — لا نخمن، نتجاوزها بأمان.
                    continue;
                }

                $alreadyAssigned = $user->userRoles()
                    ->where('role_id', $role->id)
                    ->whereNull('department_id')
                    ->exists();

                if (! $alreadyAssigned) {
                    $user->userRoles()->create([
                        'role_id' => $role->id,
                        'department_id' => null,
                        'assigned_at' => now(),
                        'assigned_by' => null,
                    ]);
                }
            }
        });
    }
}
