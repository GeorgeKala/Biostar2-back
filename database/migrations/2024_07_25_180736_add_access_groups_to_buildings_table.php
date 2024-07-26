<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('buildings', function (Blueprint $table) {
            $table->json('access_group')->nullable();
        });
    }

    public function down()
    {
        Schema::table('buildings', function (Blueprint $table) {
            $table->dropColumn('access_group');
        });
    }
};
