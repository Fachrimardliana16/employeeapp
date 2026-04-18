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
        Schema::table('master_standar_harga_satuans', function (Blueprint $table) {
            $table->string('code')->nullable()->after('id');
            $table->renameColumn('grade_level', 'spesifikasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_standar_harga_satuans', function (Blueprint $table) {
            $table->dropColumn('code');
            $table->renameColumn('spesifikasi', 'grade_level');
        });
    }
};
