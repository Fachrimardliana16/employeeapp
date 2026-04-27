<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds fields to track machine's reported datetime for time sync verification.
     */
    public function up(): void
    {
        Schema::table('attendance_machines', function (Blueprint $table) {
            $table->dateTime('machine_datetime')->nullable()->after('status')
                ->comment('Last known datetime reported by the machine');
            $table->dateTime('time_checked_at')->nullable()->after('machine_datetime')
                ->comment('Server time when machine_datetime was recorded');
            $table->integer('time_drift_seconds')->nullable()->after('time_checked_at')
                ->comment('Drift in seconds: machine_time - server_time. Positive = machine ahead');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_machines', function (Blueprint $table) {
            $table->dropColumn(['machine_datetime', 'time_checked_at', 'time_drift_seconds']);
        });
    }
};
