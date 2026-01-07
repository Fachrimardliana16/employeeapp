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
            $table->unsignedBigInteger('employee_education_id')->nullable()->after('basic_salary_id');
            $table->foreign('employee_education_id')->references('id')->on('master_employee_education')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_agreement', function (Blueprint $table) {
            $table->dropForeign(['employee_education_id']);
            $table->dropColumn('employee_education_id');
        });
    }
};
