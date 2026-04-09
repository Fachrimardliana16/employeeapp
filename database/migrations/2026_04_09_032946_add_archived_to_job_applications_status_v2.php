<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            // Converting to string to allow 'archived' status and bypass SQLite enum constraints
            $table->string('status')->change();
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->enum('status', [
                'submitted',
                'reviewed',
                'interview_scheduled',
                'interviewed',
                'accepted',
                'rejected',
                'withdrawn'
            ])->change();
        });
    }
};
