<?php

use App\VideoFormat;
use Illuminate\Database\Seeder;

class VideoFormatsSeeder extends Seeder
{
    private $formats = ['270p', '480p', '720p'];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->formats as $format) {
            VideoFormat::create(['title' => $format]);
        }
    }
}
