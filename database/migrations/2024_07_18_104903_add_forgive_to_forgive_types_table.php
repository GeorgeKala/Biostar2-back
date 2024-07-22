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
        Schema::table('forgive_types', function (Blueprint $table) {
            $table->boolean('forgive')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forgive_types', function (Blueprint $table) {
            $table->dropColumn('forgive'); 
        });
    }
};
