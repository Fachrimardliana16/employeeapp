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
            if (!Schema::hasColumn('employee_attendance_records', 'attendance_status')) {
                $table->string('attendance_status')->nullable()->after('photo_checkout');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_attendance_records', function (Blueprint $table) {
            if (Schema::hasColumn('employee_attendance_records', 'attendance_status')) {
                $table->dropColumn('attendance_status');
            }
        });
    }
};
