<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLineSectionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('line_section', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('line_id')->unsigned();;
            $table->integer('section_id')->unsigned();;
            $table->integer('order');
            $table->integer('mode');
            $table->foreign('line_id')->references('id')->on('lines')->onDelelete('cascade');
            $table->foreign('section_id')->references('id')->on('sections');
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
        Schema::dropIfExists('line_section');
    }
}
