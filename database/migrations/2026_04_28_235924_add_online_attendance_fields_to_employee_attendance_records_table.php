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
        Schema::table('employee_attendance_records', function (Blueprint $table) {
            // Source: machine (from ADMS) or online (selfie + GPS via web)
            if (!Schema::hasColumn('employee_attendance_records', 'source')) {
                $table->string('source')->default('machine')->after('attendance_status')
                    ->comment('machine = dari mesin absensi, online = absensi web');
            }
            // GPS accuracy in meters (from browser Geolocation API)
            if (!Schema::hasColumn('employee_attendance_records', 'gps_accuracy')) {
                $table->float('gps_accuracy')->nullable()->after('source')
                    ->comment('Akurasi GPS saat absen (meter). < 50 = baik.');
            }
            // GPS jitter: distance between first and last of 3 readings (meters)
            // Very low jitter (< 0.3m) with high accuracy = suspected mock GPS
            if (!Schema::hasColumn('employee_attendance_records', 'gps_jitter')) {
                $table->float('gps_jitter')->nullable()->after('gps_accuracy')
                    ->comment('Pergerakan antar 3 pembacaan GPS (meter). < 0.3m = mencurigakan.');
            }
            // Flag if anti-fake logic detected suspicious behavior
            if (!Schema::hasColumn('employee_attendance_records', 'is_fake_gps_suspected')) {
                $table->boolean('is_fake_gps_suspected')->default(false)->after('gps_jitter');
            }
            // Reason for flag (jitter, speed anomaly, etc.)
            if (!Schema::hasColumn('employee_attendance_records', 'gps_flag_reason')) {
                $table->string('gps_flag_reason')->nullable()->after('is_fake_gps_suspected');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_attendance_records', function (Blueprint $table) {
            $table->dropColumn([
                'source',
                'gps_accuracy',
                'gps_jitter',
                'is_fake_gps_suspected',
                'gps_flag_reason',
            ]);
        });
    }
};
