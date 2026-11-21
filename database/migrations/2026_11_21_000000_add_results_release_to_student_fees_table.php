<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // F1c — استثناء إداري (المدير فقط) يرفع حجب النتائج عن رسم معيّن بسبب مكتوب. لا يغيّر المستحق ولا المدفوع.
        Schema::table('student_fees', function (Blueprint $table) {
            $table->timestamp('results_released_at')->nullable();
            $table->foreignId('results_released_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('results_release_reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('student_fees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('results_released_by');
            $table->dropColumn(['results_released_at', 'results_release_reason']);
        });
    }
};
