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
            $table->string('proposal_docs')->nullable()->after('docs');
            $table->timestamp('applied_at')->nullable()->after('is_applied');
            $table->foreignId('applied_by')->nullable()->after('applied_at')->constrained('users')->onDelete('set null');
        });

        Schema::table('employee_promotions', function (Blueprint $table) {
            $table->string('proposal_docs')->nullable()->after('doc_promotion');
            $table->timestamp('applied_at')->nullable()->after('is_applied');
            $table->foreignId('applied_by')->nullable()->after('applied_at')->constrained('users')->onDelete('set null');
        });

        // Tables that need is_applied and other fields
        Schema::table('employee_periodic_salary_increase', function (Blueprint $table) {
            $table->boolean('is_applied')->default(false)->after('users_id');
            $table->string('proposal_docs')->nullable()->after('is_applied');
            $table->foreignId('new_employee_service_grade_id')->nullable()->after('proposal_docs')->constrained('master_employee_service_grade')->onDelete('set null');
            $table->timestamp('applied_at')->nullable()->after('new_employee_service_grade_id');
            $table->foreignId('applied_by')->nullable()->after('applied_at')->constrained('users')->onDelete('set null');
        });

        Schema::table('employee_career_movements', function (Blueprint $table) {
            $table->boolean('is_applied')->default(false)->after('users_id');
            $table->string('proposal_docs')->nullable()->after('is_applied');
            $table->timestamp('applied_at')->nullable()->after('proposal_docs');
            $table->foreignId('applied_by')->nullable()->after('applied_at')->constrained('users')->onDelete('set null');
        });

        Schema::table('employee_appointments', function (Blueprint $table) {
            $table->boolean('is_applied')->default(false)->after('users_id');
            $table->string('proposal_docs')->nullable()->after('is_applied');
            $table->timestamp('applied_at')->nullable()->after('proposal_docs');
            $table->foreignId('applied_by')->nullable()->after('applied_at')->constrained('users')->onDelete('set null');
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
            $table->dropForeign(['applied_by']);
            $table->dropColumn(['is_applied', 'proposal_docs', 'applied_at', 'applied_by']);
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
