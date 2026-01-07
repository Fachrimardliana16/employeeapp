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
        Schema::create('employee_payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('payroll_period'); // periode gaji (bulan/tahun)
            $table->decimal('base_salary', 15, 2)->default(0); // gaji pokok
            $table->decimal('total_allowance', 15, 2)->default(0); // total tunjangan
            $table->decimal('total_deduction', 15, 2)->default(0); // total potongan
            $table->decimal('total_bonus', 15, 2)->default(0); // total bonus
            $table->decimal('gross_salary', 15, 2)->default(0); // gaji kotor
            $table->decimal('net_salary', 15, 2)->default(0); // gaji bersih (take home pay)
            $table->integer('work_days')->default(0); // hari kerja
            $table->integer('present_days')->default(0); // hari hadir
            $table->integer('late_count')->default(0); // jumlah terlambat
            $table->integer('absent_count')->default(0); // jumlah absen
            $table->decimal('overtime_hours', 8, 2)->default(0); // jam lembur
            $table->enum('payment_status', ['draft', 'calculated', 'approved', 'paid'])->default('draft');
            $table->date('payment_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('users_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_payrolls');
    }
};
