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
        Schema::create('attendance_teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('shifts');
            $table->foreignId('attendance_id')->constrained('attendance_codes');
            $table->date('dates')->index();
            $table->time('check_in');
            $table->time('check_out')->nullable();
            $table->boolean('is_late')->default(false);
            $table->index(['teacher_id', 'dates']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_teachers');
    }
};
