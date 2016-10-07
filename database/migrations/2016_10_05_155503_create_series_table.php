<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSeriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('series', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('abbreviation')->length(10);
            $table->string('studio');
            $table->string('network');
            $table->date('series_begin')->nullable()->default(null);
            $table->date('series_end')->nullable()->default(null);
            $table->integer('timeline_begin')->unsigned()->nullable()->default(null);
            $table->integer('timeline_end')->unsigned()->nullable()->default(null);
            $table->integer('num_seasons')->unsigned()->nullable()->default(null);
            $table->integer('num_episodes')->unsigned()->nullable()->default(null);
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
        Schema::dropIfExists('series');
    }
}
