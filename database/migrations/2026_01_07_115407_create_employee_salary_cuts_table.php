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
        Schema::create('employee_salary_cuts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('cut_name'); // nama potongan
            $table->enum('cut_type', ['permanent', 'temporary']); // tetap atau sementara
            $table->enum('calculation_type', ['fixed', 'percentage']); // nominal tetap atau persentase
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('start_date');
            $table->date('end_date')->nullable(); // null jika permanent
            $table->integer('installment_months')->nullable(); // cicilan berapa bulan
            $table->integer('paid_months')->default(0); // sudah dibayar berapa bulan
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->foreignId('users_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_salary_cuts');
    }
};
