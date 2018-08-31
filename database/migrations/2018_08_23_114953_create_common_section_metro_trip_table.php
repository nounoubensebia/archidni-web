<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommonSectionMetroTripTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('common_section_metro_trip', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('metro_trip_id')->unsigned();
            $table->integer('common_section_id')->unsigned();
            $table->integer("station1_index");
            $table->integer('station2_index');
            $table->foreign('metro_trip_id')->references('id')->on('metro_trips')->onDelete('cascade');
            $table->foreign('common_section_id')->references('id')->on('common_sections');
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
        Schema::dropIfExists('common_section_metro_trip');
    }
}
