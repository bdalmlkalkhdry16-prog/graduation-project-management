<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 2 — Staff Profile.
     * موظفون إداريون غير أكاديميين (مثال مستقبلي: شؤون الطلاب، الكنترول).
     * لا يوجد دور "staff" في النظام القديم، لذا لا بيانات لتعبئتها الآن —
     * الجدول جاهز للاستخدام عند بناء وحدة شؤون الطلاب لاحقًا.
     */
    public function up(): void
    {
        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('position')->nullable(); // المسمى الوظيفي
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_profiles');
    }
};
