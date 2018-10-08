<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists("bus");
        Schema::dropIfExists("buses");
        Schema::create('buses', function (Blueprint $table) {
            $table->string('id',20);
            $table->primary('id');
            $table->double('latitude');
            $table->double('longitude');
            $table->dateTime('time');
            $table->integer('course')->nullable();
            $table->integer('speed');
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
        Schema::dropIfExists('bus');
    }
}
