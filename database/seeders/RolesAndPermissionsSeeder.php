<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Phase 1 — Roles & Permissions (أساس)، مُوسَّع في Phase 2
 * بإضافة دور "faculty" وصلاحيات الملفات الشخصية.
 *
 * الأدوار الثلاثة الأصلية (admin/supervisor/student) تطابق تمامًا
 * النظام القديم. "faculty" دور جديد إضافي (لا يستبدل supervisor).
 * ليست قائمة صلاحيات شاملة — كل Module جديدة تضيف صلاحياتها عند بنائها.
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
                    // Phase 2
                    'student-profiles.viewOwn',
                    // Phase 3
                    'academic-structure.viewOwn',
                    // Phase 5
                    'service-requests.manageOwn',
                    'schedules.viewOwn',
                    'attendance.viewOwn',
                    // Phase 6.1
                    'enrollments.viewOwn',
                ],
            ],
            // Phase 2 — Student/Faculty/Staff Central Profiles.
            // دور جديد منفصل عن "supervisor" وليس بديلاً له: كل مستخدم
            // supervisor في النظام القديم يحصل أيضًا على هذا الدور بجانب
            // دوره القديم (الشخص يحمل أكثر من Role)، لأن "المشرف = عضو
            // هيئة تدريس" حسب القرار المعتمد.
            'faculty' => [
                'name' => 'عضو هيئة تدريس',
                'description' => 'عضو هيئة تدريس (يشمل من كان "مشرف" في نظام مشاريع التخرج القديم)',
                'permissions' => [
                    'faculty-profiles.viewOwn',
                    // Phase 3
                    'sections.viewOwn',
                    'schedules.viewOwn',
                    'attendance.manageOwn',
                    // Phase 6.1
                    'enrollments.viewOwnSections',

                ],
            ],
            // Phase 5 — Student Affairs.
            // أول استخدام فعلي لدور staff (جدول staff_profiles كان جاهزًا
            // من Phase 2 بلا بيانات ولا دور مرتبط به).
            'staff' => [
                'name' => 'موظف شؤون طلاب',
                'description' => 'ينشئ ملفات الطلاب الرسمية بعد القبول، ويدير طلبات الخدمات',
                'permissions' => [
                    'student-profiles.manage',
                    'service-requests.manageAll',

                      'rooms.manage',
                    'schedules.manage',
                    'attendance.viewAll',
                ],
            ],
            // Phase 6.1 — دور مستقل لمسؤول شؤون الطلاب. دور staff العام قد يشمل
            // موظفين من إدارات مختلفة، لذلك لا يحمل صلاحية إدارة التسجيل؛
            // تُمنح هذه الصلاحية صراحةً لهذا الدور وحده.
            'student_affairs' => [
                'name' => 'مسؤول شؤون الطلاب',
                'description' => 'يدير تسجيل الطلاب في الشعب الدراسية (إنشاء، سحب، إنهاء)',
                'permissions' => [
                    'enrollments.manage',
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