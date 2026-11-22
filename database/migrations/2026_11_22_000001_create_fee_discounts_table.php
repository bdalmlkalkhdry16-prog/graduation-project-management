<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // F3 — خصومات/إعفاءات على رسم طالب. النسبة تُحوَّل لمبلغ مثبت وقت الإدخال، ويُلغى الخصم ولا يُحذف.
        Schema::create('fee_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_fee_id')->constrained('student_fees')->restrictOnDelete();
            $table->string('type', 10); // amount | percent
            $table->decimal('value', 12, 2);
            $table->decimal('amount', 12, 2);
            $table->text('reason');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('revoke_reason')->nullable();
            $table->timestamps();

            $table->index('student_fee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_discounts');
    }
};
