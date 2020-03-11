<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TranscodingJob extends Model
{
    const PENDING = 0;
    const SUCCESS = 1;
    const ERROR = 2;

    protected $fillable = [
        'status',
        'metadata',
        'template',
        'video_id',
        'aws_job_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transcodedVideos()
    {
        return $this->hasMany(TranscodedVideo::class);
    }
}
