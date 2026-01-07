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
        Schema::create('employee_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->decimal('basic_salary', 15, 2);
            $table->decimal('benefits_1', 15, 2)->default(0);
            $table->decimal('benefits_2', 15, 2)->default(0);
            $table->decimal('benefits_3', 15, 2)->default(0);
            $table->decimal('benefits_4', 15, 2)->default(0);
            $table->decimal('benefits_5', 15, 2)->default(0);
            $table->decimal('benefits_6', 15, 2)->default(0);
            $table->decimal('benefits_7', 15, 2)->default(0);
            $table->decimal('benefits_8', 15, 2)->default(0);
            $table->decimal('amount', 15, 2); // Total amount
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
        Schema::dropIfExists('employee_salaries');
    }
};
