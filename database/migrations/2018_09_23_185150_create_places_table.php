<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlacesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('places');
        Schema::create('places', function (Blueprint $table) {
            $table->increments('id');
            $table->double('latitude');
            $table->double('longitude');
            $table->string('name',200);
            $table->string('phone_number',15)->nullable();
            $table->string('address');
            $table->string('email');
            $table->integer('place_type_id')->unsigned();
            $table->foreign('place_type_id')->references('id')->on('place_types');
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
        Schema::dropIfExists('places');
    }
}
