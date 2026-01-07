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
            $table->string('signatory_name')->nullable()->after('description');
            $table->string('signatory_position')->nullable()->after('signatory_name');
            $table->string('pdf_file_path')->nullable()->after('signatory_position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_business_travel_letters', function (Blueprint $table) {
            $table->dropColumn(['signatory_name', 'signatory_position', 'pdf_file_path']);
        });
    }
};
