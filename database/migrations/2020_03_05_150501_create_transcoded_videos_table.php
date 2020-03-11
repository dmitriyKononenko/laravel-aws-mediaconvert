<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranscodedVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transcoded_videos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('output_id');
            $table->unsignedInteger('transcoding_job_id');
            $table->unsignedInteger('video_id');
            $table->string('s3_path');
            $table->json('params');
            $table->timestamps();

            $table->foreign('transcoding_job_id')->references('id')->on('transcoding_jobs');
            $table->foreign('output_id')->references('id')->on('outputs');
            $table->foreign('video_id')->references('id')->on('videos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transcoded_videos');
    }
}
