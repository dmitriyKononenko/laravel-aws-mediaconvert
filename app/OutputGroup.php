<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OutputGroup extends Model
{
    protected $fillable = [
        'name',
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
