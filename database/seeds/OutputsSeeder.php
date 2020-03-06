<?php

use App\Output;
use Illuminate\Database\Seeder;

class OutputsSeeder extends Seeder
{
    private $configs = [
        [
            'name' => '480x270p',
            'output_group_id' => 1,
            'video_format_id' => 1,
            'config' => '
                {
                    "Preset": "EduVOD_Ott_Hls_Ts_Avc_Aac_16x9_480x270p_15Hz_0.4Mbps_qvbr",
                    "NameModifier": "_Hls_480x270"
                }
            ',
        ],
        [
            'name' => '640x480p',
            'output_group_id' => 1,
            'video_format_id' => 2,
            'config' => '
                {
                    "Preset": "System-Ott_Hls_Ts_Avc_Aac_4x3_640x480p_30Hz_0.6Mbps",
                    "NameModifier": "_Hls_640x480"
                }
            ',
        ],
        [
            'name' => '1280x720p',
            'output_group_id' => 1,
            'video_format_id' => 3,
            'config' => '
              {
                    "Preset": "EduVOD_Ott_Hls_Ts_Avc_Aac_16x9_1280x720p_30Hz_5.0Mbps_qvbr",
                    "NameModifier": "_Ott_Hls_Ts_Avc_Aac_16x9_1280x720_30Hz_5.0Mbps_qvbr"
              }
            ',
        ]
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->configs as $config) {
            Output::create($config);
        }
    }
}
