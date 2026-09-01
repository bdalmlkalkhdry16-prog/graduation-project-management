<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 3 — Academic Structure.
     *
     * "البرنامج" = تخصص + مستواه (دبلوم/بكالوريوس). لا تعديل على جدول
     * specializations القديم إطلاقًا — duration_years يبقى مصدر الحقيقة
     * لمدة الدراسة هناك؛ هذا الجدول يضيف فقط "level" و"إجمالي الساعات"
     * وهما غير موجودين على specializations أصلاً.
     */
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specialization_id')->unique()->constrained('specializations')->cascadeOnDelete();
            $table->enum('level', ['diploma', 'bachelor']);
            $table->unsignedSmallInteger('total_credit_hours')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
