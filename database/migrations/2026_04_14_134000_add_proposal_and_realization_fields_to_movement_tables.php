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
        // Tables that already have is_applied
        Schema::table('employee_mutations', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_mutations', 'proposal_docs')) {
                $table->string('proposal_docs')->nullable()->after('docs');
            }
            if (!Schema::hasColumn('employee_mutations', 'applied_at')) {
                $table->timestamp('applied_at')->nullable()->after('is_applied');
            }
            if (!Schema::hasColumn('employee_mutations', 'applied_by')) {
                $table->foreignId('applied_by')->nullable()->after('applied_at')->constrained('users')->onDelete('set null');
            }
        });

        Schema::table('employee_promotions', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_promotions', 'proposal_docs')) {
                $table->string('proposal_docs')->nullable()->after('doc_promotion');
            }
            if (!Schema::hasColumn('employee_promotions', 'applied_at')) {
                $table->timestamp('applied_at')->nullable()->after('is_applied');
            }
            if (!Schema::hasColumn('employee_promotions', 'applied_by')) {
                $table->foreignId('applied_by')->nullable()->after('applied_at')->constrained('users')->onDelete('set null');
            }
        });

        // Tables that need is_applied and other fields
        Schema::table('employee_periodic_salary_increase', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_periodic_salary_increase', 'is_applied')) {
                $table->boolean('is_applied')->default(false)->after('users_id');
            }
            if (!Schema::hasColumn('employee_periodic_salary_increase', 'proposal_docs')) {
                $table->string('proposal_docs')->nullable()->after('is_applied');
            }
            if (!Schema::hasColumn('employee_periodic_salary_increase', 'new_employee_service_grade_id')) {
                $table->unsignedBigInteger('new_employee_service_grade_id')->nullable()->after('proposal_docs');
                $table->foreign('new_employee_service_grade_id', 'epsi_new_grade_foreign')
                    ->references('id')->on('master_employee_service_grade')
                    ->onDelete('set null');
            }
            if (!Schema::hasColumn('employee_periodic_salary_increase', 'applied_at')) {
                $table->timestamp('applied_at')->nullable()->after('new_employee_service_grade_id');
            }
            if (!Schema::hasColumn('employee_periodic_salary_increase', 'applied_by')) {
                $table->foreignId('applied_by')->nullable()->after('applied_at')->constrained('users')->onDelete('set null');
            }
        });

        Schema::table('employee_career_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_career_movements', 'is_applied')) {
                $table->boolean('is_applied')->default(false)->after('users_id');
            }
            if (!Schema::hasColumn('employee_career_movements', 'proposal_docs')) {
                $table->string('proposal_docs')->nullable()->after('is_applied');
            }
            if (!Schema::hasColumn('employee_career_movements', 'applied_at')) {
                $table->timestamp('applied_at')->nullable()->after('proposal_docs');
            }
            if (!Schema::hasColumn('employee_career_movements', 'applied_by')) {
                $table->foreignId('applied_by')->nullable()->after('applied_at')->constrained('users')->onDelete('set null');
            }
        });

        Schema::table('employee_appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_appointments', 'is_applied')) {
                $table->boolean('is_applied')->default(false)->after('users_id');
            }
            if (!Schema::hasColumn('employee_appointments', 'proposal_docs')) {
                $table->string('proposal_docs')->nullable()->after('is_applied');
            }
            if (!Schema::hasColumn('employee_appointments', 'applied_at')) {
                $table->timestamp('applied_at')->nullable()->after('proposal_docs');
            }
            if (!Schema::hasColumn('employee_appointments', 'applied_by')) {
                $table->foreignId('applied_by')->nullable()->after('applied_at')->constrained('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_mutations', function (Blueprint $table) {
            $table->dropForeign(['applied_by']);
            $table->dropColumn(['proposal_docs', 'applied_at', 'applied_by']);
        });

        Schema::table('employee_promotions', function (Blueprint $table) {
            $table->dropForeign(['applied_by']);
            $table->dropColumn(['proposal_docs', 'applied_at', 'applied_by']);
        });

        Schema::table('employee_periodic_salary_increase', function (Blueprint $table) {
            $table->dropForeign('epsi_new_grade_foreign');
            $table->dropForeign(['applied_by']);
            $table->dropColumn(['is_applied', 'proposal_docs', 'new_employee_service_grade_id', 'applied_at', 'applied_by']);
        });

        Schema::table('employee_career_movements', function (Blueprint $table) {
            $table->dropForeign(['applied_by']);
            $table->dropColumn(['is_applied', 'proposal_docs', 'applied_at', 'applied_by']);
        });

        Schema::table('employee_appointments', function (Blueprint $table) {
            $table->dropForeign(['applied_by']);
            $table->dropColumn(['is_applied', 'proposal_docs', 'applied_at', 'applied_by']);
        });
    }
};
