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
        Schema::create('attendance_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('shifts');
            $table->foreignId('attendance_id')->constrained('attendance_codes');
            $table->date('dates')->index();
            $table->time('check_in');
            $table->time('check_out')->nullable();
            $table->boolean('is_late')->default(false);
            $table->index(['student_id', 'dates']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_students');
    }
};
