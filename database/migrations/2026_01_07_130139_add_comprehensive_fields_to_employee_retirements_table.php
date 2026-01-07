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
            $table->enum('retirement_type', ['resign', 'pension', 'contract_end', 'termination'])->after('employee_id')->default('resign');
            $table->date('last_working_day')->after('retirement_date')->nullable();
            $table->text('handover_notes')->after('reason')->nullable();
            $table->text('company_assets')->after('handover_notes')->nullable();
            $table->string('handover_document')->after('company_assets')->nullable();
            $table->string('forwarding_address', 500)->after('docs')->nullable();
            $table->string('forwarding_phone', 20)->after('forwarding_address')->nullable();
            $table->string('forwarding_email')->after('forwarding_phone')->nullable();
            $table->boolean('need_reference_letter')->after('forwarding_email')->default(false);
            $table->boolean('agree_exit_interview')->after('need_reference_letter')->default(true);
            $table->text('feedback')->after('agree_exit_interview')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_retirements', function (Blueprint $table) {
            $table->dropColumn([
                'retirement_type',
                'last_working_day',
                'handover_notes',
                'company_assets',
                'handover_document',
                'forwarding_address',
                'forwarding_phone',
                'forwarding_email',
                'need_reference_letter',
                'agree_exit_interview',
                'feedback'
            ]);
        });
    }
};
