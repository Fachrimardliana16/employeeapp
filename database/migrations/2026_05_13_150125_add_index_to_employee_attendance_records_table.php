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
        Schema::table('employee_attendance_records', function (Blueprint $table) {
            $table->index('attendance_time', 'ear_attendance_time_index');
            $table->index('pin', 'ear_pin_index');
            $table->index(['attendance_time', 'state'], 'ear_time_state_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_attendance_records', function (Blueprint $table) {
            $table->dropIndex('ear_attendance_time_index');
            $table->dropIndex('ear_pin_index');
            $table->dropIndex('ear_time_state_index');
        });
    }
};
