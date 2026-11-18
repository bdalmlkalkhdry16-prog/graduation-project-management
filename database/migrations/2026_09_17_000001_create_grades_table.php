<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->restrictOnDelete();
            // الربط بالتسجيل (لا بالطالب مباشرة) يضمن على مستوى القاعدة أن الدرجة لطالب مسجَّل في شعبة الاختبار
            $table->foreignId('enrollment_id')->constrained('enrollments')->restrictOnDelete();
            $table->decimal('score', 6, 2)->nullable();
            // absent_unresolved: غياب مسجَّل لم تُحسم لائحته بعد (لا يُحوَّل إلى صفر ولا يُنشر)
            $table->enum('status', ['scored', 'absent_unresolved'])->default('scored');
            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['exam_id', 'enrollment_id']);
            $table->index('enrollment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
