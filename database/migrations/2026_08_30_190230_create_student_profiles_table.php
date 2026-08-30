<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 2 — Student Central Profile.
     * جدول جديد بالكامل. لا يعدل users.student_id القديم ولا يحذفه.
     * number_student هو رقم القيد الرسمي الجديد (مصدر الحقيقة الجديد).
     */
    public function up(): void
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('number_student')->unique();
            $table->foreignId('specialization_id')->nullable()->constrained('specializations')->nullOnDelete();
            $table->enum('program_level', ['diploma', 'bachelor'])->nullable();
            $table->unsignedTinyInteger('level')->nullable(); // المستوى/الفرقة الدراسية الحالية
            $table->unsignedSmallInteger('admission_year')->nullable();
            $table->enum('academic_status', ['active', 'suspended', 'withdrawn', 'graduated'])
                ->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};
