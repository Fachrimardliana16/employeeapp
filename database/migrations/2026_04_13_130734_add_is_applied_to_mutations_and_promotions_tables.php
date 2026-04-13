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
        Schema::table('employee_mutations', function (Blueprint $table) {
            $table->boolean('is_applied')->default(false)->after('users_id');
        });

        Schema::table('employee_promotions', function (Blueprint $table) {
            $table->boolean('is_applied')->default(false)->after('users_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_mutations', function (Blueprint $table) {
            $table->dropColumn('is_applied');
        });

        Schema::table('employee_promotions', function (Blueprint $table) {
            $table->dropColumn('is_applied');
        });
    }
};
