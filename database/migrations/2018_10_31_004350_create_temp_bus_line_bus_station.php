<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTempBusLineBusStation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('temp_bus_line_bus_station', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("line_id")->unsigned();
            $table->integer("station_id")->unsigned();
            $table->foreign('line_id')->references('id')->on('temp_bus_lines');
            $table->foreign('station_id')->references('id')->on('temp_bus_stations');
            $table->integer('type');
            $table->integer('position');
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
        //
    }
}
