<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OutputGroup extends Model
{
    const BUCKET_REPLACEMENT = '#bucket#';
    const FOLDER_REPLACEMENT = '#folder#';
    const OUTPUT_REPLACEMENT = '"#output#"';

    protected $fillable = [
        'name',
        'slug',
        'config',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function outputs()
    {
        return $this->hasMany(Output::class);
    }
}
