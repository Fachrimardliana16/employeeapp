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
        Schema::table('attendance_special_schedules', function (Blueprint $table) {
            $table->enum('type', ['libur_nasional', 'cuti_bersama', 'lainnya'])
                ->default('lainnya')
                ->after('is_working')
                ->comment('Tipe jadwal khusus: libur_nasional, cuti_bersama, lainnya');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_special_schedules', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
