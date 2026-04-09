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
        Schema::create('employee_appointments', function (Blueprint $table) {
            $table->id();
            $table->string('decision_letter_number');
            $table->date('appointment_date');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('old_employment_status_id')->nullable()->constrained('master_employee_status_employments')->onDelete('set null');
            $table->foreignId('new_employment_status_id')->constrained('master_employee_status_employments')->onDelete('cascade');
            $table->string('docs')->nullable(); // Document path
            $table->text('desc')->nullable();
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
        Schema::dropIfExists('employee_appointments');
    }
};
