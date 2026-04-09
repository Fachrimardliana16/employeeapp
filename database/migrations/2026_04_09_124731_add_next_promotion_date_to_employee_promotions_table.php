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
        Schema::table('employee_promotions', function (Blueprint $table) {
            $table->date('next_promotion_date')->nullable()->after('promotion_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_promotions', function (Blueprint $table) {
            $table->dropColumn('next_promotion_date');
        });
    }
};
