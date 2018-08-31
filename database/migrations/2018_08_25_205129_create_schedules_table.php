<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('schedules');
        Schema::create('schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('line_id')->unsigned();
            $table->foreign('line_id')->references('id')->on('lines')->onDelete('cascade');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('days')->unsigned();
            $table->integer('waiting_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedules');
    }
}
