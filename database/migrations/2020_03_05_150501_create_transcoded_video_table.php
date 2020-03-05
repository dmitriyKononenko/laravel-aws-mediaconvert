<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranscodedVideoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transcoded_video', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('output_id');
            $table->unsignedInteger('transcoding_job_id');
            $table->string('s3_path');
            $table->timestamps();

            $table->foreign('transcoding_job_id')->references('id')->on('transcoding_job');
            $table->foreign('output_id')->references('id')->on('output');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transcoded_video');
    }
}
