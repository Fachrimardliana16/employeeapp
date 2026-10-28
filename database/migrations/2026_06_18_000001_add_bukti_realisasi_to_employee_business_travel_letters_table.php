<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_business_travel_letters', function (Blueprint $table) {
            $table->json('bukti_realisasi')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('employee_business_travel_letters', function (Blueprint $table) {
            $table->dropColumn('bukti_realisasi');
        });
    }
};