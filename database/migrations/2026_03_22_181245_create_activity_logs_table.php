// database/migrations/xxxx_xx_xx_xxxxxx_create_activity_logs_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action'); // create, update, delete, view, etc.
            $table->string('model_type')->nullable(); // اسم المودل
            $table->unsignedBigInteger('model_id')->nullable(); // ID العنصر
            $table->text('old_values')->nullable(); // القيم القديمة (JSON)
            $table->text('new_values')->nullable(); // القيم الجديدة (JSON)
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index(['model_type', 'model_id']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
