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
        if (Schema::hasTable('employee_assignment_letters')) {
            Schema::table('employee_assignment_letters', function (Blueprint $table) {
                if (!Schema::hasColumn('employee_assignment_letters', 'visit_file_path')) {
                    $table->string('visit_file_path')->nullable()->after('signed_file_path');
                }
            });
        }

        if (Schema::hasTable('employee_business_travel_letters')) {
            Schema::table('employee_business_travel_letters', function (Blueprint $table) {
                if (!Schema::hasColumn('employee_business_travel_letters', 'visit_file_path')) {
                    $table->string('visit_file_path')->nullable()->after('signed_file_path');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('employee_assignment_letters')) {
            Schema::table('employee_assignment_letters', function (Blueprint $table) {
                $table->dropColumn('visit_file_path');
            });
        }

        if (Schema::hasTable('employee_business_travel_letters')) {
            Schema::table('employee_business_travel_letters', function (Blueprint $table) {
                $table->dropColumn('visit_file_path');
            });
        }
    }
};
