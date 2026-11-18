<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            // المقرر والفصل الدراسي يُشتقان من الشعبة (section.course / section.academicTerm)
            $table->foreignId('section_id')->constrained('sections')->restrictOnDelete();
            $table->string('name', 150);
            $table->enum('type', ['midterm', 'final', 'quiz', 'practical']);
            $table->date('exam_date');
            $table->time('start_time')->nullable();
            $table->unsignedSmallInteger('duration_minutes');
            // الدرجة النهائية للاختبار (أقصى ما يمكن رصده)
            $table->decimal('max_score', 6, 2);
            // الدرجة المخصصة للاختبار من مجموع المقرر
            $table->decimal('weight', 6, 2);

            // دورة النتائج
            $table->enum('result_status', ['draft', 'submitted', 'approved', 'published', 'rejected'])->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['section_id', 'name']);
            $table->index(['section_id', 'exam_date']);
            $table->index('result_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
