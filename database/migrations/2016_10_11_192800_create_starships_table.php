<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStarshipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('starships', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('class')->nullable()->default(null);
            $table->string('registry_number')->nullable()->default(null);
            $table->string('owners')->nullable()->default(null);
            $table->string('operators')->nullable()->default(null);
            $table->string('status')->nullable()->default(null);
            $table->string('status_at')->nullable()->default(null);
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
        Schema::dropIfExists('starships');
    }
}
