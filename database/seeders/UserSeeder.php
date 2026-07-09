<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Specialization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // مدير النظام
        User::create([
            'name' => 'مدير النظام',
            'email' => 'admin@graduation.com',
            'email_verified_at' => now(),
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'employee_id' => 'ADMIN001',
            'phone' => '0500000001',
            'is_active' => true,
        ]);

        // مشرف
        User::create([
            'name' => 'د. أحمد محمد',
            'email' => 'ahmed@graduation.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'role' => 'supervisor',
            'employee_id' => 'SUP001',
            'phone' => '0500000010',
            'is_active' => true,
        ]);

        // طالب
        $specialization = Specialization::first();

        User::create([
            'name' => 'أحمد خالد',
            'email' => 'ahmed@student.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'role' => 'student',
            'student_id' => '20240001',
            'phone' => '0512345678',
            'specialization_id' => $specialization->id ?? null,
            'is_active' => true,
        ]);

        $this->command->info('✅ تم إضافة المستخدمين');
    }
}
