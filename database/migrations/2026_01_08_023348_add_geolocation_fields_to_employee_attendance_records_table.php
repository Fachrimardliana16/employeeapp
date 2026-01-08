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
            // Geolocation fields
            $table->foreignId('office_location_id')->nullable()->after('picture')->constrained('master_office_locations')->nullOnDelete();
            $table->decimal('check_latitude', 10, 8)->nullable()->after('office_location_id');
            $table->decimal('check_longitude', 11, 8)->nullable()->after('check_latitude');
            $table->integer('distance_from_office')->nullable()->after('check_longitude')->comment('Distance in meters');
            $table->boolean('is_within_radius')->default(false)->after('distance_from_office');

            // Camera photo fields
            $table->string('photo_checkin')->nullable()->after('is_within_radius');
            $table->string('photo_checkout')->nullable()->after('photo_checkin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_attendance_records', function (Blueprint $table) {
            $table->dropForeign(['office_location_id']);
            $table->dropColumn([
                'office_location_id',
                'check_latitude',
                'check_longitude',
                'distance_from_office',
                'is_within_radius',
                'photo_checkin',
                'photo_checkout',
            ]);
        });
    }
};
