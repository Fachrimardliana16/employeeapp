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
        Schema::table('employees', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        Schema::table('employee_attendance_records', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        Schema::table('attendance_machine_logs', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        Schema::table('employee_assignment_letters', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        Schema::table('employee_business_travel_letters', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('employee_attendance_records', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('attendance_machine_logs', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('employee_assignment_letters', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('employee_business_travel_letters', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
