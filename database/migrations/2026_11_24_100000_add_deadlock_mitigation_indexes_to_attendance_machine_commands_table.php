<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!$this->indexExists('attendance_machine_commands', 'amc_machine_status_sent_idx')) {
            Schema::table('attendance_machine_commands', function (Blueprint $table) {
                $table->index(
                    ['attendance_machine_id', 'status', 'sent_at'],
                    'amc_machine_status_sent_idx'
                );
            });
        }

        if (!$this->indexExists('attendance_machine_commands', 'amc_machine_status_created_idx')) {
            Schema::table('attendance_machine_commands', function (Blueprint $table) {
                $table->index(
                    ['attendance_machine_id', 'status', 'created_at'],
                    'amc_machine_status_created_idx'
                );
            });
        }

        // NOTE:
        // Column "command" is TEXT, so MySQL cannot index it without prefix length.
        // We intentionally skip command-based composite index to keep migration portable.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->indexExists('attendance_machine_commands', 'amc_machine_status_sent_idx')) {
            Schema::table('attendance_machine_commands', function (Blueprint $table) {
                $table->dropIndex('amc_machine_status_sent_idx');
            });
        }

        if ($this->indexExists('attendance_machine_commands', 'amc_machine_status_created_idx')) {
            Schema::table('attendance_machine_commands', function (Blueprint $table) {
                $table->dropIndex('amc_machine_status_created_idx');
            });
        }
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $tableName)
            ->where('index_name', $indexName)
            ->exists();
    }
};
