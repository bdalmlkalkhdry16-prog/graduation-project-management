<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Phase 1 — Roles & Permissions.
 *
 * يُنشئ فقط الأدوار الثلاثة المطابقة تمامًا للنظام القديم (admin/supervisor/student)
 * ومجموعة صلاحيات ابتدائية تمثيلية تعكس ما يفعله النظام القديم فعليًا اليوم.
 * ليست قائمة شاملة — كل Module جديدة تضيف صلاحياتها عند بنائها لاحقًا.
 *
 * Idempotent بالكامل: يمكن تشغيله أكثر من مرة بأمان (updateOrCreate).
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'admin' => [
                'name' => 'مدير النظام',
                'description' => 'صلاحيات كاملة على النظام (يماثل isAdmin() في النظام القديم)',
                'permissions' => ['system.fullAccess'],
            ],
            'supervisor' => [
                'name' => 'مشرف',
                'description' => 'مشرف مشاريع تخرج (سيُربط لاحقًا بعضو هيئة التدريس عند بناء Faculty Profile)',
                'permissions' => [
                    'projects.reviewAsSupervisor',
                    'evaluations.manage',
                    'supervisor-change-requests.review',
                    'development-requests.review',
                ],
            ],
            'student' => [
                'name' => 'طالب',
                'description' => 'طالب — صاحب فكرة/مشروع تخرج',
                'permissions' => [
                    'projects.manageOwn',
                    'ideas.submitOwn',
                    'project-files.uploadOwn',
                    'comments.createOwn',
                ],
            ],
        ];

        // إنشاء كل الصلاحيات أولاً
        $allPermissions = collect($roles)->flatMap(fn ($r) => $r['permissions'])->unique();

        foreach ($allPermissions as $slug) {
            Permission::updateOrCreate(
                ['slug' => $slug],
                ['module' => explode('.', $slug)[0]]
            );
        }

        // إنشاء الأدوار وربطها بصلاحياتها
        foreach ($roles as $slug => $data) {
            $role = Role::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'is_system' => true,
                ]
            );

            $permissionIds = Permission::whereIn('slug', $data['permissions'])->pluck('id');
            $role->permissions()->syncWithoutDetaching($permissionIds);
        }
    }
}
