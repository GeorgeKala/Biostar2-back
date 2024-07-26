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
        Schema::table('employee_day_details', function (Blueprint $table) {
            $table->float('final_penalized_time', 8, 2)->nullable();
            $table->timestamp('comment_datetime')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_day_details', function (Blueprint $table) {
            $table->dropColumn('final_penalized_time');
            $table->dropColumn('comment_datetime');
        });
    }
};
