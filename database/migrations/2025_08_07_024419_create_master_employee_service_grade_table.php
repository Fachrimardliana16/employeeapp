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
        Schema::create('master_employee_service_grade', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_grade_id')->nullable()->constrained('master_employee_grades')->onDelete('cascade');
            $table->string('service_grade');
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
        Schema::dropIfExists('master_employee_service_grade');
    }
};
