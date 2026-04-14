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
                if (!Schema::hasColumn('employee_assignment_letters', 'status')) {
                    $table->string('status')->default('on progress')->after('registration_number');
                }
                if (!Schema::hasColumn('employee_assignment_letters', 'signed_file_path')) {
                    $table->string('signed_file_path')->nullable()->after('pdf_file_path');
                }
            });

            // Ensure existing records have default status
            \Illuminate\Support\Facades\DB::table('employee_assignment_letters')
                ->whereNull('status')
                ->update(['status' => 'on progress']);
        }

        if (Schema::hasTable('employee_business_travel_letters')) {
            Schema::table('employee_business_travel_letters', function (Blueprint $table) {
                if (!Schema::hasColumn('employee_business_travel_letters', 'status')) {
                    $table->string('status')->default('on progress')->after('registration_number');
                }
                if (!Schema::hasColumn('employee_business_travel_letters', 'signed_file_path')) {
                    $table->string('signed_file_path')->nullable()->after('pdf_file_path');
                }
            });

            // Ensure existing records have default status
            \Illuminate\Support\Facades\DB::table('employee_business_travel_letters')
                ->whereNull('status')
                ->update(['status' => 'on progress']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('employee_assignment_letters')) {
            Schema::table('employee_assignment_letters', function (Blueprint $table) {
                $table->dropColumn(['status', 'signed_file_path']);
            });
        }

        if (Schema::hasTable('employee_business_travel_letters')) {
            Schema::table('employee_business_travel_letters', function (Blueprint $table) {
                $table->dropColumn(['status', 'signed_file_path']);
            });
        }
    }
};
