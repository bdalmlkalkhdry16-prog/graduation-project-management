<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // F1b — رسوم الطالب عن سنة دراسية. المبلغ والعملة نسخة مثبتة وقت التوليد من سعر القسم،
        // فتعديل السعر لاحقًا لا يغيّر ما استُحق على الطالب.
        Schema::create('student_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained('student_profiles')->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->foreignId('fee_schedule_id')->constrained('fee_schedules')->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->enum('currency', ['YER', 'USD']);
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['student_profile_id', 'academic_year_id']);
            $table->index('academic_year_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fees');
    }
};
