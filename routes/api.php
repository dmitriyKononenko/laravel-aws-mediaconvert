<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/videos/{id}', 'VideoController@show'); // Get single video
Route::post('/videos/{id}/transcode', 'VideoController@transcode'); // Run transcode job for specific video
Route::get('/videos', 'VideoController@index'); // Get videos list
Route::post('/videos', 'VideoController@upload'); // Upload and transcode video

Route::post('/sns/toggleJobSuccess', 'AwsSnsController'); // Aws SNS events endpoint
