<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEpisodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('episodes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->integer('series_id')->unsigned();
            $table->integer('season_num')->unsigned();
            $table->integer('episode_num')->unsigned();
            $table->string('serial_number');
            $table->date('air_date');
            $table->timestamps();

            $table->foreign('series_id')->references('id')->on('series');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('episodes', function (Blueprint $table) {
            $table->dropForeign(['series_id']);
        });

        Schema::dropIfExists('episodes');
    }
}
