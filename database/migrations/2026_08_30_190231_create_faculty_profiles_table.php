<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 2 — Faculty Profile.
     * عضو هيئة التدريس. المشرف في نظام مشاريع التخرج القديم هو نفس هذا
     * الشخص (users.role = supervisor) — لا FK جديد على جداول مشاريع
     * التخرج، الربط عبر user_id فقط.
     */
    public function up(): void
    {
        Schema::create('faculty_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('specialization_id')->nullable()->constrained('specializations')->nullOnDelete();
            $table->string('academic_rank')->nullable(); // الرتبة الأكاديمية
            $table->unsignedSmallInteger('hiring_year')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faculty_profiles');
    }
};
