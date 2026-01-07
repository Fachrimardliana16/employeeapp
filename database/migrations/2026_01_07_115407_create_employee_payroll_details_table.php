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
        Schema::create('employee_payroll_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_payroll_id')->constrained('employee_payrolls')->onDelete('cascade');
            $table->foreignId('payroll_component_id')->constrained('payroll_components')->onDelete('cascade');
            $table->string('component_name'); // denormalisasi untuk history
            $table->enum('component_type', ['income', 'deduction', 'bonus']);
            $table->decimal('amount', 15, 2)->default(0);
            $table->text('calculation_note')->nullable(); // catatan perhitungan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_payroll_details');
    }
};
