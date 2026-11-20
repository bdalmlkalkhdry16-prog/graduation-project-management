<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // F1a — الرسوم الدراسية: سعر خاص لكل قسم عن كل سنة دراسية، بالريال اليمني (القديم) أو بالدولار.
        Schema::create('fee_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->enum('currency', ['YER', 'USD']);
            $table->text('notes')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['department_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_schedules');
    }
};
