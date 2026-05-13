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
        Schema::table('attendance_machine_logs', function (Blueprint $table) {
            $table->index(['pin', 'timestamp'], 'aml_pin_timestamp_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_machine_logs', function (Blueprint $table) {
            $table->dropIndex('aml_pin_timestamp_idx');
        });
    }
};
