<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommonSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('common_sections', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("station1_id")->unsigned();
            $table->integer("station2_id")->unsigned();
            $table->foreign("station1_id")->references('id')->on('stations');
            $table->foreign("station2_id")->references('id')->on('stations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('common_sections');
    }
}
