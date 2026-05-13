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
            // defaultSort('created_at', 'desc') — setiap page load
            $table->index('created_at', 'emp_created_at_index');
            // tab filter: WHERE employment_status_id = ? di getTabs() & SelectFilter
            $table->index('employment_status_id', 'emp_employment_status_index');
            // sort by name di kolom searchable
            $table->index('name', 'emp_name_index');
            // departemen terbesar widget & filter — nama kolom yg benar: departments_id
            $table->index('departments_id', 'emp_departments_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex('emp_created_at_index');
            $table->dropIndex('emp_employment_status_index');
            $table->dropIndex('emp_name_index');
            $table->dropIndex('emp_departments_id_index');
        });
    }
};
