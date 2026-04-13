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
        Schema::table('employee_appointments', function (Blueprint $table) {
            $table->foreignId('employee_grade_id')->nullable()->after('new_employment_status_id')->constrained('master_employee_grades')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('employee_grade_id');
        });
    }
};
