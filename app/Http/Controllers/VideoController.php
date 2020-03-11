<?php

namespace App\Http\Controllers;

use App\Http\Requests\TranscodeVideoRequest;
use App\Http\Resources\VideoResource;
use App\Http\Resources\VideosResource;
use App\Video;
use App\Output;
use App\services\VideoConvertService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UploadVideoRequest;

class VideoController extends Controller
{
    /**
     * @return VideosResource|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function index()
    {
        try {
            return new VideosResource(Video::with(['transcodedVideos', 'transcodedVideos.output'])
                ->get());
        } catch (ModelNotFoundException $exception) {
            return response($exception->getMessage(), 404);
        } catch (\Exception $exception) {
            return response($exception->getMessage(), 500);
        }
    }

    /**
     * @param UploadVideoRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function upload(UploadVideoRequest $request)
    {
        try {
            $file = Storage::disk('s3')->put('/', $request->file);

            $video = Video::create([
                'name' => $request->name,
                'title' => $request->title,
                'description' => $request->description,
                's3_path' => 's3://' . env('AWS_BUCKET') . '/' . $file,
            ]);

            if (!empty($request->outputs)) {
                $service = new VideoConvertService($video);

                $outputs = Output::whereIn('id', $request->outputs)->get();

                $service->runJob($outputs);
            }

            return response(new VideosResource($video), 201);
        } catch (\Exception $exception) {
            return response($exception->getMessage(), 500);
        }
    }

    /**
     * @param int $id
     * @return VideoResource|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function show(int $id)
    {
        try {
            return new VideoResource(Video::with([
                'transcodedVideos',
                'transcodedVideos.output',
                'transcodingJobs',
            ])
                ->findOrFail($id));
        } catch (ModelNotFoundException $exception) {
            return response($exception->getMessage(), 404);
        } catch (\Exception $exception) {
            return response($exception->getMessage(), 500);
        }
    }

    /**
     * @param TranscodeVideoRequest $request
     * @param int $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function transcode(TranscodeVideoRequest $request, int $id)
    {
        try {
            $video = Video::findOrFail($id);

            $service = new VideoConvertService($video);

            $outputs = Output::whereIn('id', $request->outputs)->get();

            $service->runJob($outputs);

            return response('Transcoding job successfully created', 201);
        } catch (ModelNotFoundException $exception) {
            return response($exception->getMessage(), 404);
        } catch (\Exception $exception) {
            return response($exception->getMessage(), 500);
        }
    }
}
