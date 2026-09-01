<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 3 — Academic Structure.
     *
     * "الفصل الدراسي" يمتد من academic_years الموجود (لا تعديل عليه).
     * قيم semester تطابق enum('first','second','summer') المستخدم فعليًا
     * في projects.semester القديم، للاتساق بين النظامين.
     */
    public function up(): void
    {
        Schema::create('academic_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->enum('semester', ['first', 'second', 'summer']);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->unique(['academic_year_id', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_terms');
    }
};
