<?php

namespace Database\Seeders;

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Phase 2 — Student Central Profile.
 *
 * سكربت بيانات (Data Migration): لكل مستخدم قديم role=student ينشئ
 * StudentProfile مطابقًا لبياناته الحالية، دون تعديل أي عمود على users.
 *
 * قرار تنفيذي يحتاج تأكيدك: number_student يُعبَّأ مبدئيًا من قيمة
 * users.student_id القديمة (أقرب معرّف متاح حاليًا). إن كانت
 * users.student_id فارغة عند مستخدم ما، يُتجاوَز هذا المستخدم ولا يُنشأ
 * له ملف — لا نُخمِّن رقم قيد جديد من عندنا.
 *
 * Idempotent: لا يُنشئ ملفًا لمستخدم يملك واحدًا بالفعل.
 */
class LegacyStudentProfileDataSeeder extends Seeder
{
    public function run(): void
    {
        User::query()
            ->where('role', 'student')
            ->whereDoesntHave('studentProfile')
            ->whereNotNull('student_id')
            ->chunkById(200, function ($students) {
                foreach ($students as $student) {
                    // تحقق إضافي لمنع تعارض unique عند تكرار student_id قديم (نادر لكن ممكن)
                    if (StudentProfile::where('number_student', $student->student_id)->exists()) {
                        continue;
                    }

                    StudentProfile::create([
                        'user_id' => $student->id,
                        'number_student' => $student->student_id,
                        'specialization_id' => $student->specialization_id,
                        'academic_status' => 'active',
                    ]);
                }
            });
    }
}
