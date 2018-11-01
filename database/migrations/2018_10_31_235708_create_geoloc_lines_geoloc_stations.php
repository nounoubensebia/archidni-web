<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGeolocLinesGeolocStations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('geoloc_bus_lines_geoloc_bus_stations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("geoloc_line_id")->unsigned();
            $table->foreign('geoloc_line_id')->references('id')->on('geoloc_lines');
            $table->integer("geoloc_station_id")->unsigned();
            $table->foreign('geoloc_station_id')->references('id')->on('geoloc_stations');
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
