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
        Schema::table('employee_permissions', function (Blueprint $table) {
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('permission_desc');
            $table->foreignId('approved_by')->nullable()->after('approval_status')->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('approval_notes')->nullable()->after('approved_at');
            $table->boolean('is_resign')->default(false)->after('approval_notes'); // untuk menandai resign
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_permissions', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approval_status', 'approved_by', 'approved_at', 'approval_notes', 'is_resign']);
        });
    }
};
