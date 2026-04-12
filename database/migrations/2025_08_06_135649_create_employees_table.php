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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('nippam')->unique()->nullable();
            $table->string('name');
            $table->string('place_birth')->nullable();
            $table->date('date_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('religion')->nullable();
            $table->integer('age')->nullable();
            $table->text('address')->nullable();
            $table->string('blood_type')->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->string('phone_number')->nullable();
            $table->string('id_number')->nullable();
            $table->string('familycard_number')->nullable();
            $table->string('npwp_number')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bpjs_tk_number')->nullable();
            $table->string('bpjs_kes_number')->nullable();
            $table->string('rek_dplk_pribadi')->nullable();
            $table->string('rek_dplk_bersama')->nullable();
            $table->string('username')->unique()->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('password')->nullable();
            $table->string('image')->nullable();
            $table->integer('leave_balance')->default(0);
            $table->date('entry_date')->nullable();
            $table->date('probation_appointment_date')->nullable();
            $table->integer('length_service')->nullable();
            $table->date('retirement')->nullable();

            // Foreign keys
            $table->foreignId('employment_status_id')->nullable()->constrained('master_employee_status_employments')->onDelete('set null');
            $table->foreignId('master_employee_agreement_id')->nullable()->constrained('master_employee_agreements')->onDelete('set null');
            $table->date('agreement_date_start')->nullable();
            $table->date('agreement_date_end')->nullable();
            $table->foreignId('employee_education_id')->nullable()->constrained('master_employee_education')->onDelete('set null');
            $table->date('grade_date_start')->nullable();
            $table->date('grade_date_end')->nullable();
            $table->foreignId('basic_salary_id')->nullable()->constrained('master_employee_grades')->onDelete('set null');
            $table->date('periodic_salary_date_start')->nullable();
            $table->date('periodic_salary_date_end')->nullable();
            $table->foreignId('employee_position_id')->nullable()->constrained('master_employee_positions')->onDelete('set null');
            $table->foreignId('departments_id')->nullable()->constrained('master_departments')->onDelete('set null');
            $table->foreignId('sub_department_id')->nullable()->constrained('master_departments')->onDelete('set null');
            $table->foreignId('users_id')->constrained('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
