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
            $table->decimal('accommodation_reserve_cost', 15, 2)->nullable()->after('shs_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_business_travel_letters', function (Blueprint $table) {
            $table->dropColumn('accommodation_reserve_cost');
        });
    }
};
