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
        Schema::create('payroll_components', function (Blueprint $table) {
            $table->id();
            $table->string('component_name'); // Gaji Pokok, Tunjangan Jabatan, Tunjangan Transport, dll
            $table->string('component_code')->unique(); // GP, TJ, TT, dll
            $table->enum('component_type', ['income', 'deduction', 'bonus']); // pendapatan, potongan, bonus
            $table->enum('calculation_method', ['fixed', 'percentage', 'formula']); // tetap, persentase, formula
            $table->decimal('default_amount', 15, 2)->default(0);
            $table->text('formula')->nullable(); // untuk calculation_method = formula
            $table->boolean('is_taxable')->default(true); // kena pajak atau tidak
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
        Schema::dropIfExists('payroll_components');
    }
};
