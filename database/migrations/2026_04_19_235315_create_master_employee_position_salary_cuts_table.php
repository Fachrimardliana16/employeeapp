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
        Schema::create('master_employee_position_salary_cuts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_position_id');
            $table->foreign('employee_position_id', 'pos_salary_cuts_position_fk')->references('id')->on('master_employee_positions')->onDelete('cascade');
            $table->unsignedBigInteger('salary_cuts_id');
            $table->foreign('salary_cuts_id', 'pos_salary_cuts_cut_fk')->references('id')->on('master_employee_salary_cuts')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->text('desc')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('users_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_employee_position_salary_cuts');
    }
};
