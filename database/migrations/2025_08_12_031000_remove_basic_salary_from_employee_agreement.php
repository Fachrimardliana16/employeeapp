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
            // Remove the redundant basic_salary decimal column
            $table->dropColumn('basic_salary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_agreement', function (Blueprint $table) {
            // Add back the basic_salary decimal column if needed
            $table->decimal('basic_salary', 15, 2)->after('basic_salary_id');
        });
    }
};
