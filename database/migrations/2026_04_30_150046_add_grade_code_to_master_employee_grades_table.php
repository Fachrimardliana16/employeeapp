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
            // Add grade_code column for standard PNS grade (I/a, I/b, II/a, etc.)
            $table->string('grade_code', 10)->nullable()->after('name')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_employee_grades', function (Blueprint $table) {
            $table->dropColumn('grade_code');
        });
    }
};
