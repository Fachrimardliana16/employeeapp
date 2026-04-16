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
        Schema::table('employee_periodic_salary_increase', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('docs_archive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_periodic_salary_increase', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
