<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('origin_id')->unsigned();;
            $table->integer('destination_id')->unsigned();;
            $table->foreign('origin_id')->references('id')->on('stations')->onDelete('cascade');
            $table->foreign('destination_id')->references('id')->on('stations')->onDelete('cascade');
            $table->float('duration')->nullable();
            $table->text('polyline');
            $table->float('durationPolyline')->nullable();
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
        Schema::dropIfExists('sections');
    }
}
