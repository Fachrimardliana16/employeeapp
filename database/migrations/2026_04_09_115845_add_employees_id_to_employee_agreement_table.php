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
        Schema::table('employee_agreement', function (Blueprint $table) {
            // Tambah kolom employees_id sebagai FK langsung ke employees
            // nullable agar record lama (dari jalur rekrutmen) tidak terpengaruh
            $table->unsignedBigInteger('employees_id')
                ->nullable()
                ->after('job_application_archives_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_agreement', function (Blueprint $table) {
            $table->dropColumn('employees_id');
        });
    }
};
