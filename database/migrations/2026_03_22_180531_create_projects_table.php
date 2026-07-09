<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title_ar');
            $table->string('title_en')->nullable();
            $table->text('abstract_ar');
            $table->text('abstract_en')->nullable();
            $table->text('keywords')->nullable();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->onDelete('restrict');
            $table->foreignId('specialization_id')->constrained('specializations')->onDelete('restrict');
            $table->enum('status', ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'completed'])->default('draft');
            $table->integer('academic_year')->nullable();
            $table->enum('semester', ['first', 'second', 'summer'])->nullable();
            $table->float('success_percentage')->nullable();
            $table->text('feedback')->nullable();
            $table->date('submission_date')->nullable();
            $table->date('approval_date')->nullable();
            $table->date('defense_date')->nullable();
            $table->boolean('idea_approved')->default(false);
            $table->text('idea_review_notes')->nullable();
            $table->timestamp('idea_submitted_at')->nullable();
            $table->timestamps();

            $table->index('supervisor_id');
            $table->index('specialization_id');
            $table->index('status');
            $table->index('academic_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};