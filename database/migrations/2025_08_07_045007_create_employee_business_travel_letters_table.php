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
        Schema::create('employee_business_travel_letters', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number');
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('destination');
            $table->text('destination_detail')->nullable();
            $table->text('purpose_of_trip');
            $table->decimal('business_trip_expenses', 15, 2)->default(0);
            $table->string('pasal')->nullable();
            $table->foreignId('employee_signatory_id')->constrained('employees')->onDelete('cascade');
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
        Schema::dropIfExists('employee_business_travel_letters');
    }
};
