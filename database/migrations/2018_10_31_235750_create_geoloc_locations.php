<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGeolocLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('geoloc_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("geoloc_station_id")->unsigned();
            $table->foreign('geoloc_station_id')->references('id')->on('geoloc_stations');
            $table->double('latitude');
            $table->double('longitude');
            $table->integer('arrival');
            $table->integer('gps_precision');
            $table->integer('time_diff');
            $table->dateTime('timest');
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
