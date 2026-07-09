// database/migrations/xxxx_xx_xx_xxxxxx_create_specializations_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specializations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->integer('duration_years')->default(2); // مدة الدراسة بالسنوات
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('department_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specializations');
    }
};
