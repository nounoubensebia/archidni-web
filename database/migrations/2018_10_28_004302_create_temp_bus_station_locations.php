<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTempBusStationLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('temp_bus_station_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("station_id")->unsigned();
            $table->foreign('station_id')->references('id')->on('temp_bus_stations');
            $table->double("latitude");
            $table->double("longitude");
            $table->integer("is_verified");
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
