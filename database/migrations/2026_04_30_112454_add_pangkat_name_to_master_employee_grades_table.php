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
        Schema::table('master_employee_grades', function (Blueprint $table) {
            $table->string('pangkat_name')->nullable()->after('name')->comment('Nama pangkat sesuai PP Pasal 8');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_employee_grades', function (Blueprint $table) {
            $table->dropColumn('pangkat_name');
        });
    }
};
