<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutputTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('output', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('output_group_id');
            $table->json('config');
            $table->unsignedInteger('video_format_id');
            $table->timestamps();

            $table->foreign('output_group_id')->references('id')->on('output_group');
            $table->foreign('video_format_id')->references('id')->on('video_format');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('output');
    }
}
