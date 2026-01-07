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
            $table->unsignedBigInteger('signatory_employee_id')->nullable()->after('signatory_position');
            $table->foreign('signatory_employee_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_business_travel_letters', function (Blueprint $table) {
            $table->dropForeign(['signatory_employee_id']);
            $table->dropColumn('signatory_employee_id');
        });
    }
};
