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
        Schema::table('master_office_locations', function (Blueprint $table) {
            $table->foreignId('departments_id')->nullable()->after('radius')->constrained('master_departments')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_office_locations', function (Blueprint $table) {
            $table->dropForeign(['departments_id']);
            $table->dropColumn('departments_id');
        });
    }
};
