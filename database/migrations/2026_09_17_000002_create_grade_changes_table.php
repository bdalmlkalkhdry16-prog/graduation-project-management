<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->constrained('grades')->restrictOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('action', ['entered', 'updated', 'corrected']);
            $table->decimal('old_score', 6, 2)->nullable();
            $table->decimal('new_score', 6, 2)->nullable();
            $table->string('old_status', 30)->nullable();
            $table->string('new_status', 30)->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index('grade_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_changes');
    }
};
