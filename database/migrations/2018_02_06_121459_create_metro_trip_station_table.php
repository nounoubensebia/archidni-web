<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMetroTripStationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('metro_trip_station', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('metro_trip_id')->unsigned();;
            $table->integer('station_id')->unsigned();;
            $table->integer('minutes');
            $table->foreign('metro_trip_id')->references('id')->on('metro_trips')->onDelete('cascade');
            $table->foreign('station_id')->references('id')->on('stations');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('metro_trip_station');
    }
}
