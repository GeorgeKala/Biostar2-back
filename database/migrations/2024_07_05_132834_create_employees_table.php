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
            $table->string('fullname');
            $table->string('personal_id')->unique();
            $table->string('phone_number');
            $table->string('position');
            $table->dateTime('start_datetime');
            $table->dateTime('expiry_datetime')->nullable();
            $table->integer('honorable_minutes_per_day')->nullable();
            $table->string('device')->nullable();
            $table->string('card_number');
            $table->string('checksum')->nullable();
            $table->foreignId('department_id')->constrained();
            $table->foreignId('group_id')->constrained();
            $table->foreignId('schedule_id')->constrained('schedules');
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
