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
        Schema::table('job_application_archives', function (Blueprint $table) {
            $table->unsignedBigInteger('proposed_non_permanent_salary_id')->nullable()->after('proposed_grade_id');
            $table->foreign('proposed_non_permanent_salary_id', 'jaa_non_perm_salary_fk')
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
        Schema::table('job_application_archives', function (Blueprint $table) {
            $table->dropForeign('jaa_non_perm_salary_fk');
            $table->dropColumn('proposed_non_permanent_salary_id');
        });
    }
};
