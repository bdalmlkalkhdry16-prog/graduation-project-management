<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إصلاح معماري بعد مراجعة Phase 3 (بدون تعديل migration الإنشاء الأصلي).
     *
     * المشكلة: sections.faculty_id كان يشير مباشرة لـ users.id (تقليدًا
     * لنمط Project.supervisor_id القديم)، متجاوزًا faculty_profiles التي
     * بنيناها في Phase 2 خصيصًا كهوية عضو هيئة التدريس المرجعية. هذا لا
     * يفرض على مستوى قاعدة البيانات أن المُدرِّس له ملف أكاديمي فعلي.
     *
     * هذا الإصلاح خاص بجدول sections (Phase 3، جدول جديد بالكامل) فقط.
     * Project.supervisor_id القديم لا علاقة له بهذا الملف ولم يُلمَس.
     *
     * الحل: استبدال faculty_id (→ users.id) بـ faculty_profile_id
     * (→ faculty_profiles.id).
     */
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropForeign(['faculty_id']);
            $table->dropColumn('faculty_id');
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->foreignId('faculty_profile_id')->nullable()->after('academic_term_id')
                ->constrained('faculty_profiles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropForeign(['faculty_profile_id']);
            $table->dropColumn('faculty_profile_id');
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->foreignId('faculty_id')->nullable()->after('academic_term_id')
                ->constrained('users')->nullOnDelete();
        });
    }
};
