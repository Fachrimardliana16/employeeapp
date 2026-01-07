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
        Schema::create('master_employee_grade_benefit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_grade_id')->constrained('master_employee_grades')->onDelete('cascade');
            $table->foreignId('benefit_id')->constrained('master_employee_benefits')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->text('desc')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('users_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_employee_grade_benefit');
    }
};
