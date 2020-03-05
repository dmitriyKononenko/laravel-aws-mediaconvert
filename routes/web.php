<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Http\Request;

function getOutputPresets(array $required_variants): array {
    $variants = collect([
        '270p' => [
            'Preset' => 'EduVOD_Ott_Hls_Ts_Avc_Aac_16x9_480x270p_15Hz_0.4Mbps_qvbr',
            'NameModifier' => '_Ott_Hls_Ts_Avc_Aac_16x9_480x270_15Hz_0.4Mbps_qvbr',
        ],
        '480p' => [
            'NameModifier' => '_Ott_Hls_Ts_Avc_Aac_4x3_640x480_30Hz_0.6Mbps',
            'Preset' => 'System-Ott_Hls_Ts_Avc_Aac_4x3_640x480p_30Hz_0.6Mbps',
        ],
        '720p' => [
            'NameModifier' => '_Ott_Hls_Ts_Avc_Aac_16x9_1280x720_30Hz_5.0Mbps_qvbr',
            'Preset' => 'EduVOD_Ott_Hls_Ts_Avc_Aac_16x9_1280x720p_30Hz_5.0Mbps_qvbr',
        ],
    ]);

    return $variants->filter(function($value, $key) use ($required_variants) {
        return in_array($key, $required_variants);
    })->toArray();
}

Route::get('/job/{id}', function(Request $request, $id) {
    $client = AWS::createClient('MediaConvert', [
        'version' => '2017-08-29',
        'region' => 'us-east-1',
        'profile' => 'default',
        'endpoint' => 'https://q25wbt2lc.mediaconvert.us-east-1.amazonaws.com',
    ]);

    $result = $client->getJob([
        'Id' => $id, // REQUIRED
    ]);

    dd($result);
});

Route::get('/', function (Request $request) {
//    $s3 = AWS::createClient('s3');
//    $result = $s3->putObject(array(
//        'Bucket'     => 'eduvod-source-1h6392o80rgzg',
//        'Key'        => 'test.file',
//        'SourceFile' => public_path() . '/robots.txt',
//    ));
//
//    dd($result);

//    $client = new MediaConvertClient([
//        'version' => '2017-08-29',
//        'region' => 'us-east-1',
//        'profile' => 'default',
//        'endpoint' => 'https://q25wbt2lc.mediaconvert.us-east-1.amazonaws.com'
//    ]);

    $client = AWS::createClient('MediaConvert', [
        'version' => '2017-08-29',
        'region' => 'us-east-1',
        'profile' => 'default',
        'endpoint' => 'https://q25wbt2lc.mediaconvert.us-east-1.amazonaws.com',
    ]);

    $jobTemplate = $client->getJobTemplate([
        'Name' => 'Media_Convert_HLS_Test',
    ])['JobTemplate'];

    // Append input file source
    $jobTemplate['Settings']['Inputs'][0]['FileInput'] = 's3://eduvod-source-1h6392o80rgzg/TestHls.mp4';

    // Append outputs
    $jobTemplate['Settings']['OutputGroups'][0]['Outputs'] = [];
    foreach (getOutputPresets(['720p', '270p']) as $output) {
        $jobTemplate['Settings']['OutputGroups'][0]['Outputs'][] = $output;
    }

    if (empty($jobTemplate['Settings']['OutputGroups'][0]['Outputs'])) {
        dd($jobTemplate);
    }

    // Append role arn
    $jobTemplate['Role'] = 'arn:aws:iam::226709391673:role/EduVOD-MediaConvertRole-11W188QPQB9B5';

    dump($jobTemplate);

        try {
            $result = $client->createJob($jobTemplate);
            dd($result);
        } catch (\Exception $exception) {
            dd($exception);
        };

    return view('welcome');
});
