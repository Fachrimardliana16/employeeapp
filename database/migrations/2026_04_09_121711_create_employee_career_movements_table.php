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
        Schema::create('employee_career_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->enum('type', ['promotion', 'demotion'])->default('promotion');
            $table->string('decision_letter_number')->nullable();
            $table->date('movement_date');
            
            // Previous Data (Historical)
            $table->foreignId('old_department_id')->nullable()->constrained('master_departments');
            $table->foreignId('old_sub_department_id')->nullable()->constrained('master_sub_departments');
            $table->foreignId('old_position_id')->nullable()->constrained('master_employee_positions');
            
            // New Data
            $table->foreignId('new_department_id')->nullable()->constrained('master_departments');
            $table->foreignId('new_sub_department_id')->nullable()->constrained('master_sub_departments');
            $table->foreignId('new_position_id')->nullable()->constrained('master_employee_positions');
            
            $table->string('doc_path')->nullable();
            $table->text('description')->nullable();
            
            $table->foreignId('users_id')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_career_movements');
    }
};
