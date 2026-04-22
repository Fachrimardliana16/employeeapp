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
        $tables = [
            'employee_retirements',
            'employee_daily_reports',
            'employee_documents',
            'employee_appointments',
            'employee_career_movements',
            'job_applications',
            'job_application_archives',
            'master_office_locations',
            'employee_families',
            'employee_permissions',
            'employee_training',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                if (!Schema::hasColumn($tableName, 'deleted_at')) {
                    Schema::table($tableName, function (Blueprint $table) {
                        $table->softDeletes()->after('updated_at');
                    });
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'employee_retirements',
            'employee_daily_reports',
            'employee_documents',
            'employee_appointments',
            'employee_career_movements',
            'job_applications',
            'job_application_archives',
            'master_office_locations',
            'employee_families',
            'employee_permissions',
            'employee_training',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                if (Schema::hasColumn($tableName, 'deleted_at')) {
                    Schema::table($tableName, function (Blueprint $table) {
                        $table->dropSoftDeletes();
                    });
                }
            }
        }
    }
};
