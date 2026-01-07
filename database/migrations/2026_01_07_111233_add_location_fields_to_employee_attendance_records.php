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
            $table->decimal('latitude', 10, 8)->nullable()->after('state');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->string('location_address')->nullable()->after('longitude');
            $table->decimal('distance_meters', 10, 2)->nullable()->after('location_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_attendance_records', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'location_address', 'distance_meters']);
        });
    }
};
