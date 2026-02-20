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
            $table->text('note')->nullable()->after('is_late');
        });

        Schema::table('attendance_teachers', function (Blueprint $table) {
            $table->text('note')->nullable()->after('is_late');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_students', function (Blueprint $table) {
            $table->dropColumn('note');
        });

        Schema::table('attendance_teachers', function (Blueprint $table) {
            $table->dropColumn('note');
        });
    }
};
