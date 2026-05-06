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
        Schema::table('employee_promotions', function (Blueprint $table) {
            // Jenis kenaikan pangkat (PP Pasal 10-16)
            $table->foreignId('promotion_type_id')
                ->nullable()
                ->after('employee_id')
                ->constrained('master_promotion_types')
                ->onDelete('restrict');
            
            // File uploads (dokumen persyaratan PP Pasal 10)
            $table->string('dpk_file')->nullable()->after('doc_promotion');
            $table->string('work_report_file')->nullable()->after('dpk_file');
            $table->string('attendance_proof')->nullable()->after('work_report_file');
            $table->string('previous_sk_file')->nullable()->after('attendance_proof');
            $table->string('diploma_file')->nullable()->after('previous_sk_file');
            
            // Workflow approval
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])
                ->default('draft')
                ->after('diploma_file');
            $table->foreignId('approved_by')
                ->nullable()
                ->after('status')
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('rejection_note')->nullable()->after('approved_at');
            
            // Index untuk performa
            $table->index(['promotion_type_id'], 'idx_promotion_type');
            $table->index(['status'], 'idx_status');
            $table->index(['promotion_date', 'status'], 'idx_date_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_promotions', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('idx_promotion_type');
            $table->dropIndex('idx_status');
            $table->dropIndex('idx_date_status');
            
            // Drop foreign keys
            $table->dropForeign(['promotion_type_id']);
            $table->dropForeign(['approved_by']);
            
            // Drop columns
            $table->dropColumn([
                'promotion_type_id',
                'dpk_file',
                'work_report_file',
                'attendance_proof',
                'previous_sk_file',
                'diploma_file',
                'status',
                'approved_by',
                'approved_at',
                'rejection_note',
            ]);
        });
    }
};
