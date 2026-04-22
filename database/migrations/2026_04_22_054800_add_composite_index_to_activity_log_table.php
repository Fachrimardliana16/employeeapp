<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            // Composite index untuk query halaman Activity Log
            // (filter by log_name + sort by created_at)
            $table->index(['log_name', 'created_at'], 'activity_log_name_created_index');
        });
    }

    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropIndex('activity_log_name_created_index');
        });
    }
};
