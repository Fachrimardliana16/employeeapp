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
        Schema::create('employee_periodic_salary_increase', function (Blueprint $table) {
            $table->id();
            $table->string('number_psi'); // Periodic Salary Increase Number
            $table->date('date_periodic_salary_increase');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('old_basic_salary_id')->constrained('master_employee_grades')->onDelete('cascade');
            $table->foreignId('new_basic_salary_id')->constrained('master_employee_grades')->onDelete('cascade');
            $table->decimal('total_basic_salary', 15, 2);
            $table->string('docs_letter')->nullable(); // Letter document path
            $table->string('docs_archive')->nullable(); // Archive document path
            $table->foreignId('users_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_periodic_salary_increase');
    }
};
