<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStarshipClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('starship_classes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('owner')->nullable()->default(null);
            $table->string('operator')->nullable()->default(null);
            $table->string('active_during')->nullable()->default(null);
            $table->string('affiliation')->nullable()->default(null);
            $table->string('type')->nullable()->default(null);
            $table->string('length')->nullable()->default(null);
            $table->string('mass')->nullable()->default(null);
            $table->string('speed')->nullable()->default(null);
            $table->string('decks')->nullable()->default(null);
            $table->string('armanent')->nullable()->default(null);
            $table->string('defenses')->nullable()->default(null);
            $table->string('crew')->nullable()->default(null);
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
        Schema::dropIfExists('starship_classes');
    }
}
