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
            'employee_benefits',
            'employee_mutations',
            'master_employee_non_permanent_salaries',
            'employee_promotions',
            'master_employee_retirement_types',
            'employee_salaries',
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
            'employee_benefits',
            'employee_mutations',
            'master_employee_non_permanent_salaries',
            'employee_promotions',
            'master_employee_retirement_types',
            'employee_salaries',
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
