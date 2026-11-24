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
        Schema::table('attendance_machine_commands', function (Blueprint $table) {
            $table->index(
                ['attendance_machine_id', 'status', 'sent_at'],
                'amc_machine_status_sent_idx'
            );
            $table->index(
                ['attendance_machine_id', 'status', 'created_at'],
                'amc_machine_status_created_idx'
            );
            $table->index(
                ['attendance_machine_id', 'command', 'status'],
                'amc_machine_command_status_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_machine_commands', function (Blueprint $table) {
            $table->dropIndex('amc_machine_status_sent_idx');
            $table->dropIndex('amc_machine_status_created_idx');
            $table->dropIndex('amc_machine_command_status_idx');
        });
    }
};
