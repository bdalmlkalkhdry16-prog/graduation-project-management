<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            // null = دور عام غير محدود بقسم معين (مثال: admin)
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'role_id', 'department_id'], 'user_role_department_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
