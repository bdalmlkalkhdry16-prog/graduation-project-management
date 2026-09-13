<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->restrictOnDelete();
            $table->enum('day_of_week', ['sat', 'sun', 'mon', 'tue', 'wed', 'thu', 'fri']);
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();

            $table->index(['room_id', 'day_of_week']);
            $table->index(['section_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};