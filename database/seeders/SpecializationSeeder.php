<?php

namespace Database\Seeders;

use App\Models\Specialization;
use App\Models\Department;
use Illuminate\Database\Seeder;

class SpecializationSeeder extends Seeder
{
    public function run(): void
    {
        $specializations = [
            // قسم علوم حاسوب (CS)
            ['dept_code' => 'CS', 'name_ar' => 'هندسة برمجيات', 'name_en' => 'Software Engineering', 'code' => 'SWE', 'duration_years' => 4],
            ['dept_code' => 'CS', 'name_ar' => 'شبكات', 'name_en' => 'Networks', 'code' => 'NET', 'duration_years' => 4],
            ['dept_code' => 'CS', 'name_ar' => 'قواعد بيانات', 'name_en' => 'Database', 'code' => 'DB', 'duration_years' => 4],
            ['dept_code' => 'CS', 'name_ar' => 'ذكاء اصطناعي', 'name_en' => 'Artificial Intelligence', 'code' => 'AI', 'duration_years' => 4],

            // قسم نظم معلومات (IS)
            ['dept_code' => 'IS', 'name_ar' => 'نظم معلومات إدارية', 'name_en' => 'Management Information Systems', 'code' => 'MIS', 'duration_years' => 4],
            ['dept_code' => 'IS', 'name_ar' => 'تحليل نظم', 'name_en' => 'System Analysis', 'code' => 'SA', 'duration_years' => 4],

            // قسم أمن سيبراني (SEC)
            ['dept_code' => 'SEC', 'name_ar' => 'أمن الشبكات', 'name_en' => 'Network Security', 'code' => 'NS', 'duration_years' => 4],
            ['dept_code' => 'SEC', 'name_ar' => 'اختبار الاختراق', 'name_en' => 'Penetration Testing', 'code' => 'PT', 'duration_years' => 4],

            // قسم محاسبة (ACC)
            ['dept_code' => 'ACC', 'name_ar' => 'محاسبة مالية', 'name_en' => 'Financial Accounting', 'code' => 'FA', 'duration_years' => 4],
            ['dept_code' => 'ACC', 'name_ar' => 'محاسبة تكاليف', 'name_en' => 'Cost Accounting', 'code' => 'CA', 'duration_years' => 4],

            // قسم إدارة أعمال (BA)
            ['dept_code' => 'BA', 'name_ar' => 'إدارة مشاريع', 'name_en' => 'Project Management', 'code' => 'PM', 'duration_years' => 4],
            ['dept_code' => 'BA', 'name_ar' => 'ريادة أعمال', 'name_en' => 'Entrepreneurship', 'code' => 'ENT', 'duration_years' => 4],

            // قسم تسويق (MKT)
            ['dept_code' => 'MKT', 'name_ar' => 'تسويق رقمي', 'name_en' => 'Digital Marketing', 'code' => 'DM', 'duration_years' => 4],
            ['dept_code' => 'MKT', 'name_ar' => 'علاقات عامة', 'name_en' => 'Public Relations', 'code' => 'PR', 'duration_years' => 4],

            // قسم هندسة مدنية (CIV)
            ['dept_code' => 'CIV', 'name_ar' => 'هندسة إنشائية', 'name_en' => 'Structural Engineering', 'code' => 'STR', 'duration_years' => 4],
            ['dept_code' => 'CIV', 'name_ar' => 'هندسة نقل', 'name_en' => 'Transportation Engineering', 'code' => 'TRN', 'duration_years' => 4],

            // قسم هندسة كهربائية (ELE)
            ['dept_code' => 'ELE', 'name_ar' => 'أنظمة تحكم', 'name_en' => 'Control Systems', 'code' => 'CTL', 'duration_years' => 4],
            ['dept_code' => 'ELE', 'name_ar' => 'طاقة متجددة', 'name_en' => 'Renewable Energy', 'code' => 'REN', 'duration_years' => 4],
        ];

        $count = 0;
        foreach ($specializations as $spec) {
            $department = Department::where('code', $spec['dept_code'])->first();
            if ($department) {
                Specialization::create([
                    'department_id' => $department->id,
                    'name_ar' => $spec['name_ar'],
                    'name_en' => $spec['name_en'],
                    'code' => $spec['code'],
                    'description' => "تخصص {$spec['name_ar']}",
                    'duration_years' => $spec['duration_years'],
                    'is_active' => true,
                ]);
                $count++;
            }
        }

        $this->command->info('✅ تم إضافة ' . $count . ' تخصص بنجاح');
    }
}
