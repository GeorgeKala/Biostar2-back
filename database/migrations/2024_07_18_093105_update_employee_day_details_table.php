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
        Schema::table('employee_day_details', function (Blueprint $table) {
            $table->dropColumn('day_type');
            $table->unsignedBigInteger('day_type_id')->nullable();
            $table->foreign('day_type_id')->references('id')->on('day_types')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_day_details', function (Blueprint $table) {
            $table->dropForeign(['day_type_id']);
            $table->dropColumn('day_type_id');
            $table->string('day_type')->nullable();
        });
    }
};
