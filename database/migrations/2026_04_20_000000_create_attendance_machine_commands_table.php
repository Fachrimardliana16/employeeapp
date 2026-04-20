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
        Schema::create('attendance_machine_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_machine_id')->constrained()->cascadeOnDelete();
            $table->text('command');
            $table->enum('status', ['pending', 'sent', 'completed', 'failed'])->default('pending');
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->text('response_payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_machine_commands');
    }
};
