<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إصلاح معماري بعد مراجعة Phase 3 (بدون تعديل migration الإنشاء الأصلي).
     *
     * المشكلة: unique() مفرد على specialization_id يمنع أن يُقدَّم نفس
     * التخصص بأكثر من مستوى (دبلوم وبكالوريوس معًا) — سيناريو حقيقي محتمل.
     *
     * الحل: استبداله بقيد مركّب unique(specialization_id, level) — يمنع
     * فقط تكرار نفس (تخصص + مستوى)، ويسمح بمستويين مختلفين لنفس التخصص.
     */
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropUnique('programs_specialization_id_unique');
            $table->unique(['specialization_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropUnique(['specialization_id', 'level']);
            $table->unique('specialization_id');
        });
    }
};
