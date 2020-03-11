<?php

namespace App\Listeners;

use App\services\VideoConvertService;

class MediaConvertJobSuccessListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        VideoConvertService::jobSuccess($event->message);
    }
}
