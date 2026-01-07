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
        Schema::create('employee_mutations', function (Blueprint $table) {
            $table->id();
            $table->string('decision_letter_number');
            $table->date('mutation_date');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('old_department_id')->constrained('master_departments')->onDelete('cascade');
            $table->foreignId('old_sub_department_id')->nullable()->constrained('master_sub_departments')->onDelete('cascade');
            $table->foreignId('new_department_id')->constrained('master_departments')->onDelete('cascade');
            $table->foreignId('new_sub_department_id')->nullable()->constrained('master_sub_departments')->onDelete('cascade');
            $table->foreignId('old_position_id')->constrained('master_employee_positions')->onDelete('cascade');
            $table->foreignId('new_position_id')->constrained('master_employee_positions')->onDelete('cascade');
            $table->string('docs')->nullable(); // Document path
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
        Schema::dropIfExists('employee_mutations');
    }
};
