<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            // جعل العمودين قابلين للقيم الفارغة
            $table->integer('academic_year')->nullable()->change();
            $table->enum('semester', ['first', 'second', 'summer'])->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->integer('academic_year')->nullable(false)->change();
            $table->enum('semester', ['first', 'second', 'summer'])->nullable(false)->change();
        });
    }
};
