<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = [
        'name',
        'title',
        'description',
        's3_path',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transcodingJobs()
    {
        return $this->hasMany(TranscodingJob::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transcodedVideos()
    {
        return $this->hasMany(TranscodedVideo::class);
    }
}
