<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranscodingJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transcoding_jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger('status');
            $table->json('template');
            $table->json('metadata')->nullable();
            $table->unsignedInteger('video_id');
            $table->string('aws_job_id');
            $table->timestamps();

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
        Schema::dropIfExists('transcoding_jobs');
    }
}
