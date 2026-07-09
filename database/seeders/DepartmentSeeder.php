<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\College;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            // كلية الهندسة (ENG)
            ['college_code' => 'ENG', 'name_ar' => 'هندسة مدنية', 'name_en' => 'Civil Engineering', 'code' => 'CIV', 'description' => 'قسم الهندسة المدنية'],
            ['college_code' => 'ENG', 'name_ar' => 'هندسة كهربائية', 'name_en' => 'Electrical Engineering', 'code' => 'ELE', 'description' => 'قسم الهندسة الكهربائية'],
            ['college_code' => 'ENG', 'name_ar' => 'هندسة ميكانيكية', 'name_en' => 'Mechanical Engineering', 'code' => 'MEC', 'description' => 'قسم الهندسة الميكانيكية'],

            // كلية تقنية المعلومات (IT)
            ['college_code' => 'IT', 'name_ar' => 'علوم حاسوب', 'name_en' => 'Computer Science', 'code' => 'CS', 'description' => 'قسم علوم الحاسوب'],
            ['college_code' => 'IT', 'name_ar' => 'نظم معلومات', 'name_en' => 'Information Systems', 'code' => 'IS', 'description' => 'قسم نظم المعلومات'],
            ['college_code' => 'IT', 'name_ar' => 'أمن سيبراني', 'name_en' => 'Cyber Security', 'code' => 'SEC', 'description' => 'قسم الأمن السيبراني'],

            // كلية العلوم الإدارية (ADMIN)
            ['college_code' => 'ADMIN', 'name_ar' => 'محاسبة', 'name_en' => 'Accounting', 'code' => 'ACC', 'description' => 'قسم المحاسبة'],
            ['college_code' => 'ADMIN', 'name_ar' => 'تسويق', 'name_en' => 'Marketing', 'code' => 'MKT', 'description' => 'قسم التسويق'],
            ['college_code' => 'ADMIN', 'name_ar' => 'إدارة أعمال', 'name_en' => 'Business Administration', 'code' => 'BA', 'description' => 'قسم إدارة الأعمال'],

            // كلية العلوم الطبية (MED)
            ['college_code' => 'MED', 'name_ar' => 'تمريض', 'name_en' => 'Nursing', 'code' => 'NRS', 'description' => 'قسم التمريض'],
            ['college_code' => 'MED', 'name_ar' => 'صيدلة', 'name_en' => 'Pharmacy', 'code' => 'PHA', 'description' => 'قسم الصيدلة'],

            // كلية الآداب (ARTS)
            ['college_code' => 'ARTS', 'name_ar' => 'لغة عربية', 'name_en' => 'Arabic Language', 'code' => 'AR', 'description' => 'قسم اللغة العربية'],
            ['college_code' => 'ARTS', 'name_ar' => 'لغة إنجليزية', 'name_en' => 'English Language', 'code' => 'EN', 'description' => 'قسم اللغة الإنجليزية'],
        ];

        foreach ($departments as $dept) {
            $college = College::where('code', $dept['college_code'])->first();
            if ($college) {
                Department::create([
                    'college_id' => $college->id,
                    'name_ar' => $dept['name_ar'],
                    'name_en' => $dept['name_en'],
                    'code' => $dept['code'],
                    'description' => $dept['description'],
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('✅ تم إضافة ' . count($departments) . ' قسم بنجاح');
    }
}
