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
        Schema::create('master_employee_non_permanent_salaries', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Gaji Standar THL", "Gaji Magang"
            $table->foreignId('employment_status_id')->constrained('master_employee_status_employments', indexName: 'mens_emp_status_fk')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->boolean('is_active')->default(true);
            $table->foreignId('users_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_employee_non_permanent_salaries');
    }
};
