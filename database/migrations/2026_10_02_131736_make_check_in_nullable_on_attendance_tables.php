<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendance_students', function (Blueprint $table) {
            $table->time('check_in')->nullable()->change();
        });

        Schema::table('attendance_teachers', function (Blueprint $table) {
            $table->time('check_in')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_students', function (Blueprint $table) {
            $table->time('check_in')->nullable(false)->change();
        });

        Schema::table('attendance_teachers', function (Blueprint $table) {
            $table->time('check_in')->nullable(false)->change();
        });
    }
};
