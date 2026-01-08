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
        Schema::table('employee_retirements', function (Blueprint $table) {
            $table->foreignId('master_employee_retirement_type_id')->nullable()->after('employee_id')->constrained('master_employee_retirement_types')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_retirements', function (Blueprint $table) {
            $table->dropForeign(['master_employee_retirement_type_id']);
            $table->dropColumn('master_employee_retirement_type_id');
        });
    }
};
