<?php

namespace App\Http\Controllers;

use App\Events\MediaConvertJobError;
use App\Events\MediaConvertJobSuccess;
use App\Events\SnsMessageReceived;
use Aws\Sns\Exception\InvalidSnsMessageException;
use Aws\Sns\Message;
use Aws\Sns\MessageValidator;

class AwsSnsController extends Controller
{
    const SUCCESS_STATUS = 'COMPLETE';
    const ERROR_STATUS = 'ERROR';

    public function __invoke()
    {
        $message = Message::fromRawPostData();

        $validator = new MessageValidator();

        try {
            $validator->validate($message);
        } catch (InvalidSnsMessageException $e) {
            return response('SNS Message Validation Error: ' . $e->getMessage(), 404);
        }

        if (isset($message['Type']) && $message['Type'] === 'SubscriptionConfirmation') {
            file_get_contents($message['SubscribeURL']);

            return response('OK', 200);
        }

        if (isset($message['Type']) && $message['Type'] === 'Notification') {
            $message_data = json_decode($message['Message'], true);

            if ($message_data['detail-type'] === 'MediaConvert Job State Change') {
                if ($message_data['detail']['status'] === self::SUCCESS_STATUS) {
                    event(new MediaConvertJobSuccess($message));
                }

                if ($message_data['detail']['status'] === self::ERROR_STATUS) {
                    event(new MediaConvertJobError($message));
                }

                return response('OK', 200);
            }

            event(new SnsMessageReceived($message));
        }

        return response('OK', 200);
    }
}
