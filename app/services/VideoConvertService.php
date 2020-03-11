<?php


namespace App\services;

use App\Video;
use App\Output;
use Aws\Sns\Message;
use App\OutputGroup;
use App\TranscodingJob;
use App\TranscodedVideo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Aws\Credentials\CredentialProvider;
use Aws\MediaConvert\MediaConvertClient;

class VideoConvertService
{
    private $video;

    public function __construct(Video $video)
    {
        $this->video = $video;
    }

    /**
     * @param Collection $outputs
     * @return mixed
     */
    public function runJob(Collection $outputs)
    {
        $mc_job_config = $this->getJobConfig($outputs);

        $mc_client = new MediaConvertClient([
            'version' => '2017-08-29',
            'region' => env('AWS_REGION'),
            'profile' => 'default',
            'endpoint' => env('MC_ENDPOINT'),
            'credentials' => CredentialProvider::env(),
        ]);

        // Todo: Check result errors
        $job = $mc_client->createJob($mc_job_config);

        $job_data = $job['Job'];

        return TranscodingJob::create([
            'status' => TranscodingJob::PENDING,
            'template' => json_encode($mc_job_config),
            'metadata' => json_encode($job_data),
            'video_id' => $this->video->id,
            'aws_job_id' => $job_data['Id'],
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

    /**
     * @param Message $message
     */
    static function jobError(Message $message)
    {
        $message = $message->toArray();
        $message_data = json_decode($message['Message'], true);

        Log::debug('Media Convert Error Event message' . json_encode($message));

        $trasncoding_job = TranscodingJob::where('aws_job_id', $message_data['detail']['jobId'])
            ->first();

        if (empty($trasncoding_job)) {
            Log::error(
                'Cannot find job id: '
                . $message_data['detail']['jobId']
                . 'from message '
                . json_encode($message_data)
            );

            return;
        }

        $trasncoding_job->update([
            'status' => TranscodingJob::ERROR,
            'metadata' => json_encode($message_data),
        ]);
    }

    /**
     * @param Message $message
     */
    static function jobSuccess(Message $message)
    {
        $message = $message->toArray();
        $message_data = json_decode($message['Message'], true);

        Log::debug('Media Convert Success Event message' . json_encode($message));

        $trasncoding_job = TranscodingJob::where('aws_job_id', $message_data['detail']['jobId'])
            ->first();

        if (empty($trasncoding_job)) {
            Log::error(
                'Cannot find job id: '
                . $message_data['detail']['jobId']
                . 'from message '
                . json_encode($message_data)
            );

            return;
        }

        $trasncoding_job->update([
            'status' => TranscodingJob::SUCCESS,
            'metadata' => json_encode($message_data),
        ]);

        foreach ( $message_data['detail']['outputGroupDetails'] as $output_group_detail) {
            foreach ($output_group_detail['outputDetails'] as $output_detail) {
                $output_group = OutputGroup::where('type', $output_group_detail['type'])->first();

                $output_id = [];

                 preg_match('/_OID(\d+)/', $output_detail['outputFilePaths'][0], $output_id);

                 if (empty($output_id)) {
                     Log::error('Cannot find output id: ' . $output_id[1]);

                     continue;
                 }

                $output = Output::where([
                    'id'=> $output_id[1],
                    'output_group_id' => $output_group->id,
                ])->first();

                if (empty($output)) {
                    Log::error(
                        'Cannot find output with params: Output id: '
                        . $output_id[1]
                        . ' Output Group id: '
                        . $output_group->id
                    );

                    continue;
                }

                TranscodedVideo::create([
                    'output_id' => $output->id,
                    'transcoding_job_id' => $trasncoding_job->id,
                    's3_path' => $output_detail['outputFilePaths'][0],
                    'params' => $output_detail,
                    'video_id' => $trasncoding_job->video_id,
                ]);
            }
        }
    }
}
