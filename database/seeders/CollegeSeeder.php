<?php

namespace Database\Seeders;

use App\Models\College;
use Illuminate\Database\Seeder;

class CollegeSeeder extends Seeder
{
    public function run(): void
    {
        $colleges = [
            [
                'name_ar' => 'كلية الهندسة',
                'name_en' => 'College of Engineering',
                'code' => 'ENG',
                'description' => 'تقدم الكلية برامج متميزة في مجالات الهندسة المدنية والكهربائية والميكانيكية',
                'is_active' => true,
            ],
            [
                'name_ar' => 'كلية تقنية المعلومات',
                'name_en' => 'College of Information Technology',
                'code' => 'IT',
                'description' => 'تقدم الكلية برامج متخصصة في علوم الحاسوب وتقنية المعلومات والأمن السيبراني',
                'is_active' => true,
            ],
            [
                'name_ar' => 'كلية العلوم الإدارية',
                'name_en' => 'College of Administrative Sciences',
                'code' => 'ADMIN',
                'description' => 'تقدم الكلية برامج في إدارة الأعمال والمحاسبة والتسويق',
                'is_active' => true,
            ],
            [
                'name_ar' => 'كلية العلوم الطبية',
                'name_en' => 'College of Medical Sciences',
                'code' => 'MED',
                'description' => 'تقدم الكلية برامج في التمريض والصيدلة والعلوم الطبية المساعدة',
                'is_active' => true,
            ],
            [
                'name_ar' => 'كلية الآداب والعلوم الإنسانية',
                'name_en' => 'College of Arts and Humanities',
                'code' => 'ARTS',
                'description' => 'تقدم الكلية برامج في اللغات والعلوم الإنسانية والاجتماعية',
                'is_active' => true,
            ],
        ];

        foreach ($colleges as $college) {
            College::create($college);
        }

        $this->command->info('✅ تم إضافة ' . count($colleges) . ' كلية بنجاح');
    }
}
