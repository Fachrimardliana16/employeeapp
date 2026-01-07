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
        Schema::create('employee_assignment_letters', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number');
            $table->foreignId('assigning_employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('employee_position_id')->constrained('master_employee_positions')->onDelete('cascade');
            $table->text('task');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('description')->nullable();
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
        Schema::dropIfExists('employee_assignment_letters');
    }
};
