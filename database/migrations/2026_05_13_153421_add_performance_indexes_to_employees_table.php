<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $existing = collect(DB::select("SHOW INDEX FROM `employees`"))->pluck('Key_name')->unique()->all();

        Schema::table('employees', function (Blueprint $table) use ($existing) {
            if (!in_array('emp_created_at_index', $existing)) {
                $table->index('created_at', 'emp_created_at_index');
            }
            if (!in_array('emp_employment_status_index', $existing)) {
                $table->index('employment_status_id', 'emp_employment_status_index');
            }
            if (!in_array('emp_name_index', $existing)) {
                $table->index('name', 'emp_name_index');
            }
            if (!in_array('emp_departments_id_index', $existing)) {
                $table->index('departments_id', 'emp_departments_id_index');
            }
        });
    }

    public function down(): void
    {
        $existing = collect(DB::select("SHOW INDEX FROM `employees`"))->pluck('Key_name')->unique()->all();

        Schema::table('employees', function (Blueprint $table) use ($existing) {
            if (in_array('emp_created_at_index', $existing)) {
                $table->dropIndex('emp_created_at_index');
            }
            if (in_array('emp_employment_status_index', $existing)) {
                $table->dropIndex('emp_employment_status_index');
            }
            if (in_array('emp_name_index', $existing)) {
                $table->dropIndex('emp_name_index');
            }
            if (in_array('emp_departments_id_index', $existing)) {
                $table->dropIndex('emp_departments_id_index');
            }
        });
    }
};
