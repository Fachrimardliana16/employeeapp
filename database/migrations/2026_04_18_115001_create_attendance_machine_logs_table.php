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
        Schema::create('attendance_machine_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_machine_id')->constrained('attendance_machines')->onDelete('cascade');
            $table->string('serial_number');
            $table->string('pin');
            $table->dateTime('timestamp');
            $table->string('type')->comment('0: Check-In, 1: Check-Out, etc.');
            $table->text('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_machine_logs');
    }
};
