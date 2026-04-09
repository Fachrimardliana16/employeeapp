<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_application_archives', function (Blueprint $table) {
            // Changing enum values in SQLite/Laravel 12
            $table->string('decision')->change();
        });
    }

    public function down(): void
    {
        Schema::table('job_application_archives', function (Blueprint $table) {
            $table->enum('decision', ['accepted', 'rejected'])->change();
        });
    }
};
