<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // سجل التعديلات الإدارية على توزيع درجات شعبة بعد تقديم النتائج (سبب + قيم سابقة وجديدة).
        Schema::create('exam_distribution_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('sections')->restrictOnDelete();
            $table->foreignId('exam_id')->nullable()->constrained('exams')->nullOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('action', ['created', 'updated', 'deleted']);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('reason');
            $table->timestamps();

            $table->index('section_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_distribution_changes');
    }
};
