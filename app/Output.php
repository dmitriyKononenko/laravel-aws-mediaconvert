<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Output extends Model
{
    protected $fillable = [
        'name',
        'output_group_id',
        'config',
        'video_format_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function videoFormat()
    {
        return $this->belongsTo(VideoFormat::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function outputGroup()
    {
        return $this->belongsTo(OutputGroup::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transcodedVideos()
    {
        return $this->hasMany(TranscodedVideo::class);
    }
}
