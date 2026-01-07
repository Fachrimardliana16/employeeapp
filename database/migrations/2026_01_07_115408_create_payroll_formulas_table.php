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
        Schema::create('payroll_formulas', function (Blueprint $table) {
            $table->id();
            $table->string('formula_name'); // Gaji THL, Gaji Kontrak, Gaji Tetap, dll
            $table->string('formula_code')->unique();
            $table->enum('applies_to', ['status', 'grade', 'position', 'all']); // berlaku untuk apa
            $table->string('applies_to_value')->nullable(); // value dari applies_to (misal: THL, Kontrak, dll)
            $table->json('formula_components'); // {"base_salary": "grade.base_salary", "allowances": [...]}
            $table->text('calculation_rules')->nullable(); // aturan perhitungan dalam text
            $table->decimal('percentage_multiplier', 5, 2)->nullable(); // untuk capeg = 0.80 (80%)
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
        Schema::dropIfExists('payroll_formulas');
    }
};
