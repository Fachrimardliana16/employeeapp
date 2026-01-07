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
            // Kolom untuk menyimpan data pegawai tambahan dengan posisi/jabatan
            $table->json('additional_employees_detail')->nullable()->after('additional_employee_ids');

            // Kolom untuk perhitungan biaya berdasarkan SHS
            $table->decimal('accommodation_cost', 15, 2)->nullable()->after('business_trip_expenses');
            $table->decimal('pocket_money_cost', 15, 2)->nullable()->after('accommodation_cost');
            $table->decimal('reserve_cost', 15, 2)->nullable()->after('pocket_money_cost');
            $table->decimal('transport_cost', 15, 2)->nullable()->after('reserve_cost');
            $table->decimal('meal_cost', 15, 2)->nullable()->after('transport_cost');
            $table->decimal('total_cost', 15, 2)->nullable()->after('meal_cost');

            // Jumlah hari perjalanan untuk perhitungan
            $table->integer('trip_duration_days')->nullable()->after('total_cost');

            // Jumlah total pegawai (utama + tambahan)
            $table->integer('total_employees')->nullable()->after('trip_duration_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_business_travel_letters', function (Blueprint $table) {
            $table->dropColumn([
                'additional_employees_detail',
                'accommodation_cost',
                'pocket_money_cost',
                'reserve_cost',
                'transport_cost',
                'meal_cost',
                'total_cost',
                'trip_duration_days',
                'total_employees',
            ]);
        });
    }
};
