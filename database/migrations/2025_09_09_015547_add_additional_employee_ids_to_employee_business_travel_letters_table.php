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
        Schema::table('employee_business_travel_letters', function (Blueprint $table) {
            $table->json('additional_employee_ids')->nullable()->after('additional_employees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_business_travel_letters', function (Blueprint $table) {
            $table->dropColumn('additional_employee_ids');
        });
    }
};
