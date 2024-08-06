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
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->date('date');
            $table->string('week_day');
            $table->time('come_time')->nullable();
            $table->time('leave_time')->nullable();
            $table->time('come_late')->nullable();
            $table->time('come_early')->nullable();
            $table->time('leave_late')->nullable();
            $table->time('leave_early')->nullable();
            $table->decimal('worked_hours', 8, 2)->nullable();
            $table->integer('penalized_time')->nullable();
            $table->integer('final_penalized_time')->nullable();
            $table->timestamps();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
