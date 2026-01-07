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
        Schema::table('employee_assignment_letters', function (Blueprint $table) {
            $table->json('additional_employees_detail')->nullable()->after('additional_employee_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_assignment_letters', function (Blueprint $table) {
            $table->dropColumn('additional_employees_detail');
        });
    }
};
