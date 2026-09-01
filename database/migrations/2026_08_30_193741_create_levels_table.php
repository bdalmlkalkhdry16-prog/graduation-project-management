<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->unsignedTinyInteger('level_number'); // 1, 2, 3...
            $table->string('name')->nullable(); // مثال: "المستوى الأول"
            $table->timestamps();

            $table->unique(['program_id', 'level_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('levels');
    }
};
