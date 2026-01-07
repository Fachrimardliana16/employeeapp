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
        Schema::create('interview_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_application_id')->constrained('job_applications')->onDelete('cascade');
            $table->integer('interview_stage')->default(1); // tahap ke berapa
            $table->string('interview_type'); // HR, User, Psikotes, Medical, dll
            $table->date('interview_date');
            $table->time('interview_time');
            $table->string('interview_location')->nullable();
            $table->string('interviewer_name')->nullable();
            $table->foreignId('interviewer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('result', ['passed', 'failed', 'pending'])->default('pending');
            $table->integer('score')->nullable(); // nilai 0-100
            $table->text('notes')->nullable();
            $table->text('feedback')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            $table->foreignId('users_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_processes');
    }
};
