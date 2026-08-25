<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolesAndPermissionsSeederTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_the_three_legacy_matching_roles()
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertDatabaseHas('roles', ['slug' => 'admin', 'is_system' => true]);
        $this->assertDatabaseHas('roles', ['slug' => 'supervisor', 'is_system' => true]);
        $this->assertDatabaseHas('roles', ['slug' => 'student', 'is_system' => true]);
        $this->assertEquals(3, Role::count());
    }

    /** @test */
    public function it_links_roles_to_expected_permissions()
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = Role::where('slug', 'admin')->first();
        $this->assertTrue($admin->permissions->pluck('slug')->contains('system.fullAccess'));

        $supervisor = Role::where('slug', 'supervisor')->first();
        $this->assertTrue($supervisor->permissions->pluck('slug')->contains('evaluations.manage'));

        $student = Role::where('slug', 'student')->first();
        $this->assertTrue($student->permissions->pluck('slug')->contains('projects.manageOwn'));
    }

    /** @test */
    public function running_the_seeder_twice_does_not_create_duplicates()
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertEquals(3, Role::count());
        $this->assertEquals(Permission::count(), Permission::distinct('slug')->count('slug'));
    }
}
