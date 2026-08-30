<?php

namespace Tests\Unit;

use App\Models\Department;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_a_user_and_a_department()
    {
        $user = User::factory()->create(['role' => 'admin']); // لا يوجد دور staff في النظام القديم بعد
        $department = Department::factory()->create();

        $profile = StaffProfile::create([
            'user_id' => $user->id,
            'department_id' => $department->id,
            'position' => 'موظف شؤون طلاب',
        ]);

        $this->assertTrue($user->staffProfile->is($profile));
        $this->assertTrue($profile->department->is($department));
    }
}
