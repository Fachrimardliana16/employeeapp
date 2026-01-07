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
        Schema::table('employee_agreement', function (Blueprint $table) {
            $table->string('place_birth')->nullable()->after('name');
            $table->date('date_birth')->nullable()->after('place_birth');
            $table->enum('gender', ['male', 'female'])->nullable()->after('date_birth');
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable()->after('gender');
            $table->text('address')->nullable()->after('marital_status');
            $table->string('phone_number')->nullable()->after('address');
            $table->string('email')->nullable()->after('phone_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_agreement', function (Blueprint $table) {
            $table->dropColumn([
                'place_birth',
                'date_birth',
                'gender',
                'marital_status',
                'address',
                'phone_number',
                'email'
            ]);
        });
    }
};
