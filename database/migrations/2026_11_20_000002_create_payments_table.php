<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // F1b — دفعات الرسوم؛ كل دفعة لها سند قبض برقم فريد. العملة هي عملة رسم الطالب (بلا تحويل).
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_fee_id')->constrained('student_fees')->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('receipt_number', 30)->nullable()->unique();
            $table->date('paid_at');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('student_fee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
