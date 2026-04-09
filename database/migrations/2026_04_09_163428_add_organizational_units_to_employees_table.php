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
        Schema::table('employees', function (Blueprint $row) {
            $row->foreignId('bagian_id')->nullable()->constrained('master_departments')->onDelete('set null')->after('employee_position_id');
            $row->foreignId('cabang_id')->nullable()->constrained('master_departments')->onDelete('set null')->after('bagian_id');
            $row->foreignId('unit_id')->nullable()->constrained('master_departments')->onDelete('set null')->after('cabang_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $row) {
            $row->dropForeign(['bagian_id']);
            $row->dropForeign(['cabang_id']);
            $row->dropForeign(['unit_id']);
            $row->dropColumn(['bagian_id', 'cabang_id', 'unit_id']);
        });
    }
};
