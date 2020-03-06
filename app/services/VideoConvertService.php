<?php


namespace App\services;


use App\TranscodingJob;
use App\Video;
use Illuminate\Support\Collection;

class VideoConvertService
{
    private $video;

    public function __construct(Video $video)
    {
        $this->video = $video;
    }

    public function runJob(Collection $outputs): TranscodingJob
    {
        $mc_job_config = $this->getJobConfig($outputs);

        $mc_client = AWS::createClient('MediaConvert', [
            'version' => '2017-08-29',
            'region' => env('AWS_REGION'),
            'profile' => 'default',
            'endpoint' => env('MC_ENDPOINT'),
        ]);

        $job = $mc_client->createJob($mc_job_config);

        return TranscodingJob::create([
            'status' => TranscodingJob::PENDING,
            'video_id' => $this->video->id,
            'aws_job_id' => $job['Job']['id'],
        ]);
    }

    /**
     * @param Collection $outputs
     * @return array
     */
    private function getJobConfig(Collection $outputs): array
    {
        $queue_arn = env('MC_QUEUE_ARN');
        $role_arn = env('MC_ROLE_ARN');
        $output_bucket = env('MC_OUTPUT_BUCKET');

        $mc_job_builder = new MCJobBuilder(
            $queue_arn,
            $role_arn,
            $this->video->s3_path,
            $output_bucket
        );

        return $mc_job_builder->setOutputs($outputs)->build();
    }

    static function updateJobStatus(Message $message)
    {

    }
}
