<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserReportsDisruptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('user_reports_disruptions');
        Schema::create('user_reports_disruptions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('line_id')->unsigned();
            $table->foreign('line_id')->references('id')->on('lines');
            $table->integer('transport_mode_id')->unsigned();
            $table->foreign('transport_mode_id')->references('id')->on('transport_modes');
            $table->text('description');
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
        Schema::dropIfExists('user_reports_disruptions');
    }
}
