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
        Schema::create('attendance_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('day')->unique(); // Monday, Tuesday, etc.
            $table->time('check_in_start')->default('06:00:00');
            $table->time('check_in_end')->default('09:00:00');
            $table->time('check_out_start')->default('15:00:00');
            $table->time('check_out_end')->default('20:00:00');
            $table->time('late_threshold')->default('07:30:59');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_schedules');
    }
};
