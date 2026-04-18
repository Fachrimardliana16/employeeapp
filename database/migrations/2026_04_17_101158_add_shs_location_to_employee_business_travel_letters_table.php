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
            $table->string('shs_category')->nullable()->after('destination_detail');
            $table->string('shs_location')->nullable()->after('shs_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_business_travel_letters', function (Blueprint $table) {
            $table->dropColumn(['shs_category', 'shs_location']);
        });
    }
};
