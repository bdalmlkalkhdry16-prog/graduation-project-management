<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 3 — Academic Structure.
     *
     * "الشعبة": تدريس مقرر معين في فصل دراسي معين بمعرفة عضو هيئة تدريس.
     * faculty_id يشير لـ users.id مباشرة (نفس نمط Project.supervisor_id
     * القديم) — لا FK على faculty_profiles لتفادي أي تعقيد إضافي الآن.
     */
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('academic_term_id')->constrained('academic_terms')->cascadeOnDelete();
            $table->foreignId('faculty_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('code')->nullable(); // مثال: "أ", "ب"
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->timestamps();

            $table->unique(['course_id', 'academic_term_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
