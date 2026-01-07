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
        Schema::table('employees', function (Blueprint $table) {
            // Drop the incorrect foreign key constraint
            $table->dropForeign(['sub_department_id']);

            // Add the correct foreign key constraint
            $table->foreign('sub_department_id')->references('id')->on('master_sub_departments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Drop the correct foreign key constraint
            $table->dropForeign(['sub_department_id']);

            // Add back the incorrect foreign key constraint (for rollback)
            $table->foreign('sub_department_id')->references('id')->on('master_departments')->onDelete('set null');
        });
    }
};
