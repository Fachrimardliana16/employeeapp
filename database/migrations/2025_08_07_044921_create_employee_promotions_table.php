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
        Schema::create('employee_promotions', function (Blueprint $table) {
            $table->id();
            $table->string('decision_letter_number');
            $table->date('promotion_date');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('old_basic_salary_id')->constrained('master_employee_grades')->onDelete('cascade');
            $table->foreignId('new_basic_salary_id')->constrained('master_employee_grades')->onDelete('cascade');
            $table->string('doc_promotion')->nullable(); // Document path
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
        Schema::dropIfExists('employee_promotions');
    }
};
