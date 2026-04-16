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
        Schema::table('employee_retirements', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_retirements', 'is_applied')) {
                $table->boolean('is_applied')->default(false)->after('approval_status');
            }
            if (!Schema::hasColumn('employee_retirements', 'applied_at')) {
                $table->timestamp('applied_at')->nullable()->after('is_applied');
            }
            if (!Schema::hasColumn('employee_retirements', 'applied_by')) {
                $table->foreignId('applied_by')->nullable()->after('applied_at')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('employee_retirements', 'realization_docs')) {
                $table->string('realization_docs')->nullable()->after('docs');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_retirements', function (Blueprint $table) {
            $table->dropForeign(['applied_by']);
            $table->dropColumn(['is_applied', 'applied_at', 'applied_by', 'realization_docs']);
        });
    }
};
