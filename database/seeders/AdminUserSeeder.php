<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@graduation.com'],
            [
                'name' => 'مدير النظام',
                'password' => Hash::make('admin123'),
                'role' => 'Supervisor',
                'employee_id' => 'ADMIN001',
                'phone' => '0500000001',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $this->command->info('✅ تم إعداد المستخدم المدير');
    }
}
