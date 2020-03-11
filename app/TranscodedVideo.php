<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TranscodedVideo extends Model
{
    protected $fillable = [
        'output_id',
        'transcoding_job_id',
        's3_path',
        'video_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transcodingJob()
    {
        return $this->belongsTo(TranscodingJob::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function output()
    {
        return $this->belongsTo(Output::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
