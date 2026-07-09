// database/migrations/xxxx_xx_xx_xxxxxx_create_evaluations_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('supervisor_id')->constrained('users')->onDelete('cascade');
            $table->integer('creativity_score')->nullable(); // الإبداع 0-100
            $table->integer('implementation_score')->nullable(); // التنفيذ 0-100
            $table->integer('documentation_score')->nullable(); // التوثيق 0-100
            $table->integer('presentation_score')->nullable(); // العرض 0-100
            $table->float('total_percentage')->nullable();
            $table->text('strengths')->nullable(); // نقاط القوة
            $table->text('weaknesses')->nullable(); // نقاط الضعف
            $table->text('recommendations')->nullable(); // التوصيات
            $table->enum('status', ['draft', 'submitted', 'finalized'])->default('draft');
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'supervisor_id']);
            $table->index('project_id');
            $table->index('supervisor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
