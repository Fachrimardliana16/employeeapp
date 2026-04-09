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
        Schema::create('employee_families', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employees_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('master_employee_families_id')->constrained('master_employee_families')->onDelete('cascade');
            $table->string('family_name');
            $table->enum('family_gender', ['male', 'female']);
            $table->string('family_id_number', 20)->nullable();
            $table->string('family_place_birth')->nullable();
            $table->date('family_date_birth')->nullable();
            $table->text('family_address')->nullable();
            $table->string('family_phone', 20)->nullable();
            $table->boolean('is_emergency_contact')->default(false);
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
        Schema::dropIfExists('employee_families');
    }
};
