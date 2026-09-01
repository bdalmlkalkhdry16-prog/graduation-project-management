<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 3 — Academic Structure.
     *
     * تعديل على student_profiles (جدول Phase 2 الخاص بنا، وليس جدولاً من
     * النظام القديم) لإضافة الربط بالبرنامج والمستوى الحالي كجدولين
     * منظَّمين. العمود الحالي "level" (رقم بسيط من Phase 2) يبقى دون
     * تعديل أو حذف؛ current_level_id هو الإضافة المنظَّمة الجديدة.
     */
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->foreignId('program_id')->nullable()->after('specialization_id')
                ->constrained('programs')->nullOnDelete();
            $table->foreignId('current_level_id')->nullable()->after('level')
                ->constrained('levels')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('program_id');
            $table->dropConstrainedForeignId('current_level_id');
        });
    }
};
