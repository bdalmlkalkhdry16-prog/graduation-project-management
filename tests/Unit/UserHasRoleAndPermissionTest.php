<?php

namespace Tests\Unit;

use App\Models\Department;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserHasRoleAndPermissionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_without_any_role_has_no_role_or_permission()
    {
        $user = User::factory()->student()->create();

        $this->assertFalse($user->hasRole('admin'));
        $this->assertFalse($user->hasPermission('system.fullAccess'));
    }

    /** @test */
    public function assigning_a_role_grants_has_role_and_its_permissions()
    {
        $user = User::factory()->student()->create();
        $role = Role::create(['name' => 'طالب', 'slug' => 'student', 'is_system' => true]);
        $permission = Permission::create(['slug' => 'projects.manageOwn', 'module' => 'projects']);
        $role->permissions()->attach($permission);

        $user->userRoles()->create(['role_id' => $role->id]);

        $this->assertTrue($user->hasRole('student'));
        $this->assertTrue($user->hasPermission('projects.manageOwn'));
        $this->assertFalse($user->hasPermission('system.fullAccess'));
    }

    /** @test */
    public function department_scoped_permission_only_matches_its_own_department()
    {
        $deptA = Department::factory()->create();
        $deptB = Department::factory()->create();

        $user = User::factory()->supervisor()->create();
        $role = Role::create(['name' => 'رئيس قسم', 'slug' => 'department_head', 'is_system' => false]);
        $permission = Permission::create(['slug' => 'departments.manage', 'module' => 'departments']);
        $role->permissions()->attach($permission);

        $user->userRoles()->create(['role_id' => $role->id, 'department_id' => $deptA->id]);

        $this->assertTrue($user->hasPermission('departments.manage', $deptA->id));
        $this->assertFalse($user->hasPermission('departments.manage', $deptB->id));
    }

    /** @test */
    public function global_role_with_null_department_matches_any_department()
    {
        $dept = Department::factory()->create();

        $user = User::factory()->admin()->create();
        $role = Role::create(['name' => 'مدير النظام', 'slug' => 'admin', 'is_system' => true]);
        $permission = Permission::create(['slug' => 'system.fullAccess', 'module' => 'system']);
        $role->permissions()->attach($permission);

        // department_id = null (دور عام)
        $user->userRoles()->create(['role_id' => $role->id, 'department_id' => null]);

        $this->assertTrue($user->hasPermission('system.fullAccess', $dept->id));
        $this->assertTrue($user->hasPermission('system.fullAccess'));
    }

    /** @test */
    public function a_user_can_hold_more_than_one_role_at_once()
    {
        $user = User::factory()->supervisor()->create();

        $supervisorRole = Role::create(['name' => 'مشرف', 'slug' => 'supervisor', 'is_system' => true]);
        $headRole = Role::create(['name' => 'رئيس قسم', 'slug' => 'department_head', 'is_system' => false]);

        $user->userRoles()->create(['role_id' => $supervisorRole->id]);
        $user->userRoles()->create(['role_id' => $headRole->id]);

        $this->assertTrue($user->hasRole('supervisor'));
        $this->assertTrue($user->hasRole('department_head'));
        $this->assertEquals(2, $user->newRoles()->count());
    }
}
