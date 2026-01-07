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
        Schema::create('employee_training', function (Blueprint $table) {
            $table->id();
            $table->date('training_date');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('training_title');
            $table->string('training_location');
            $table->string('organizer');
            $table->string('photo_training')->nullable(); // Photo path
            $table->string('docs_training')->nullable(); // Document path
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
        Schema::dropIfExists('employee_training');
    }
};
