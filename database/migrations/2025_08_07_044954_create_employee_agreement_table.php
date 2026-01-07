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
        Schema::create('employee_agreement', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_application_archives_id')->nullable(); // Will add constraint later
            $table->string('agreement_number');
            $table->string('name');
            $table->foreignId('agreement_id')->constrained('master_employee_agreements')->onDelete('cascade');
            $table->foreignId('employee_position_id')->constrained('master_employee_positions')->onDelete('cascade');
            $table->foreignId('employment_status_id')->constrained('master_employee_status_employments')->onDelete('cascade');
            $table->foreignId('basic_salary_id')->constrained('master_employee_grades')->onDelete('cascade');
            $table->decimal('basic_salary', 15, 2);
            $table->date('agreement_date_start');
            $table->date('agreement_date_end')->nullable();
            $table->foreignId('departments_id')->constrained('master_departments')->onDelete('cascade');
            $table->foreignId('sub_department_id')->nullable()->constrained('master_sub_departments')->onDelete('cascade');
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
        Schema::dropIfExists('employee_agreement');
    }
};
