// database/migrations/xxxx_xx_xx_xxxxxx_create_evaluation_details_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained('evaluations')->onDelete('cascade');
            $table->string('criterion_name'); // اسم المعيار
            $table->integer('max_score'); // الدرجة القصوى
            $table->integer('score'); // الدرجة المحصلة
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('evaluation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_details');
    }
};
