<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // E3 — ربط طلب التظلم بتسجيل الطالب في المقرر المتظلَّم عليه (يبقى فارغًا لبقية أنواع الطلبات).
        Schema::table('student_service_requests', function (Blueprint $table) {
            $table->foreignId('enrollment_id')->nullable()->after('student_profile_id')
                ->constrained('enrollments')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_service_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('enrollment_id');
        });
    }
};
