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
        // Create communication log table
        Schema::create('attendance_machine_communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_machine_id')->nullable()->constrained('attendance_machines')->onDelete('cascade');
            $table->string('serial_number')->index();
            $table->string('endpoint'); // cdata, getrequest, devicecmd
            $table->string('method'); // GET, POST
            $table->ipAddress('ip_address')->nullable();
            $table->text('request_params')->nullable(); // Query params (SN, table, etc)
            $table->longText('request_body')->nullable(); // POST body
            $table->longText('response_body')->nullable(); // What we sent back
            $table->integer('response_code')->default(200);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['serial_number', 'created_at']);
            $table->index(['endpoint', 'created_at']);
        });

        // Add timezone and communication fields to machines table
        Schema::table('attendance_machines', function (Blueprint $table) {
            $table->string('timezone_offset')->nullable()->after('device_model'); // e.g., '+7', '+8'
            $table->boolean('auto_sync_time')->default(false)->after('timezone_offset'); // Flag to enable/disable time sync
            $table->integer('communication_success_count')->default(0)->after('auto_sync_time');
            $table->integer('communication_error_count')->default(0)->after('communication_success_count');
            $table->timestamp('last_error_at')->nullable()->after('communication_error_count');
            $table->text('last_error_message')->nullable()->after('last_error_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_machine_communications');

        Schema::table('attendance_machines', function (Blueprint $table) {
            $table->dropColumn([
                'timezone_offset',
                'auto_sync_time',
                'communication_success_count',
                'communication_error_count',
                'last_error_at',
                'last_error_message',
            ]);
        });
    }
};
