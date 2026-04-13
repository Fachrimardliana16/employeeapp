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
        Schema::table('employee_agreement', function (Blueprint $table) {
            // Make basic_salary_id nullable to support non-permanent staff
            $table->unsignedBigInteger('basic_salary_id')->nullable()->change();
            
            // Add link to non-permanent salary master
            $table->unsignedBigInteger('non_permanent_salary_id')->nullable()->after('basic_salary_id');
            $table->foreign('non_permanent_salary_id', 'ea_non_perm_salary_fk')
                ->references('id')
                ->on('master_employee_non_permanent_salaries')
                ->onDelete('set null');
        });

        Schema::table('employees', function (Blueprint $table) {
            // Add link to non-permanent salary master
            $table->unsignedBigInteger('non_permanent_salary_id')->nullable()->after('basic_salary_id');
            $table->foreign('non_permanent_salary_id', 'emp_non_perm_salary_fk')
                ->references('id')
                ->on('master_employee_non_permanent_salaries')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign('emp_non_perm_salary_fk');
            $table->dropColumn('non_permanent_salary_id');
        });

        Schema::table('employee_agreement', function (Blueprint $table) {
            $table->dropForeign('ea_non_perm_salary_fk');
            $table->dropColumn('non_permanent_salary_id');
            
            // Note: Reverting nullable basic_salary_id might fail if there are null values
            // So we leave it as nullable in down() or handle explicitly if needed.
        });
    }
};
