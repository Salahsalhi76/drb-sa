<?php

// namespace App\Jobs\Notifications;

// use Illuminate\Mail\Message;
// use Illuminate\Bus\Queueable;
// use Illuminate\Queue\SerializesModels;
// use Illuminate\Queue\InteractsWithQueue;
// use NotificationChannels\Fcm\FcmChannel;
// use NotificationChannels\Fcm\FcmMessage;
// use Illuminate\Contracts\Queue\ShouldQueue;
// use NotificationChannels\Fcm\Resources\AndroidConfig;
// use Illuminate\Foundation\Bus\Dispatchable;
// use App\Jobs\Notifications\AndroidPushNotification;
// use Illuminate\Support\Facades\Log;

// class SendPushNotification implements ShouldQueue
// {
//     use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;


//     protected $user;
//     protected $title;
//     protected $body;
//     protected $image;
//     protected $data;


//     public function __construct($user, $title, $body, $data = null, $image = null)
//     {
//         $this->user = $user;
//         $this->title = $title;
//         $this->body = $body;
//         $this->data = $data;
//         $this->image = $image;
//     }

//     /**
//      * Execute the job.
//      *
//      * @return void
//      */
//     public function handle()
//     {
//             Log::info('Sending push notification to user: ' . $this->user->id);

//         // تحقق من وجود FCM token
//         if (!$this->user->fcm_token) {
//             Log::warning('User does not have an FCM token: ' . $this->user->id);
//             return;
//         }
//         Log::info('This is the send push notification');
//         $this->user->notify(new AndroidPushNotification($this->title, $this->body, $this->data, $this->image));
//     }
// }

// ************************************* New Code **************************************
// namespace App\Jobs\Notifications;

// use App\Models\User;
// use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue;
// use Illuminate\Foundation\Bus\Dispatchable;
// use Illuminate\Queue\InteractsWithQueue;
// use Illuminate\Queue\SerializesModels;
// use Illuminate\Support\Facades\Log;

// class SendPushNotification implements ShouldQueue
// {
//     use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

//     protected $user;
//     protected $title;
//     protected $body;
//     protected $data;

//     public function __construct(User $user, $title, $body, $data = [])
//     {
//         $this->user  = $user;
//         $this->title = $title;
//         $this->body  = $body;
//         $this->data  = $data;
//     }


//     public function handle()
//     {
//         Log::info('Sending push notification to user: ' . $this->user->id);

//         $deviceTokens = [];

//         if ($this->user->fcm_token) {
//             $deviceTokens[] = $this->user->fcm_token;
//         }

//         if ($this->user->apn_token) {
//             $deviceTokens[] = $this->user->apn_token;
//         }

//         if (!empty($deviceTokens)) {
//             Log::info('Device tokens: ' . json_encode($deviceTokens));
//             Log::info('Title: ' . $this->title);
//             Log::info('Body: ' . $this->body);
//             Log::info('Data: ' . json_encode($this->data));
//             FcmPushNotification::dispatch($this->title, $this->body, $deviceTokens, $this->data);
//         } else {
//             Log::warning('No device tokens found for user: ' . $this->user->id);
//         }
//     }
    
// }


namespace App\Jobs\Notifications;

use App\Models\User;
use App\Services\FcmService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    
    protected $user;
    protected $title;
    protected $body;
    protected $data;
    protected $isTest;

    public function __construct(User $user, $title, $body, $data = [], $isTest = false)
    {
        $this->user = $user;
        $this->title = $title ?? 'بدون عنوان';
        $this->body = $body ?? '';
        $this->data = $this->prepareData($data);
        $this->isTest = $isTest;
        
    }

    public function handle(FcmService $fcmService)
    {
        try {
            Log::info('Starting SendPushNotification', [
                'user_id' => $this->user->id,
                'is_test' => $this->isTest
            ]);

            $deviceTokens = $this->getValidDeviceTokens();
            
            if (empty($deviceTokens)) {
                Log::warning('No valid device tokens found', ['user_id' => $this->user->id]);
                return;
            }

            foreach ($deviceTokens as $tokenData) {
                $this->sendNotification($fcmService, $tokenData);
            }

            Log::info('Notification processed successfully', [
                'user_id' => $this->user->id,
                'tokens_count' => count($deviceTokens)
            ]);

        } catch (\Exception $e) {
            Log::error('SendPushNotification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($this->isTest) {
                throw $e;
            }
        }
    }

    protected function prepareData($data): array
    {
        return array_map(function($value) {
            if (is_array($value) || is_object($value)) {
                return json_encode($value);
            }
            return (string)$value;
        }, is_array($data) ? $data : []);
    }

    protected function getValidDeviceTokens(): array
    {
        $tokens = [];
        
        if ($this->validateFcmToken($this->user->fcm_token)) {
            $tokens[] = [
                'type' => 'fcm',
                'token' => $this->user->fcm_token,
                'platform' => 'android'
            ];
            Log::info('Valid FCM token found', ['user_id' => $this->user->id]);
        }

        if ($this->validateApnToken($this->user->apn_token)) {
            $tokens[] = [
                'type' => 'apn',
                'token' => $this->user->apn_token,
                'platform' => 'ios'
            ];
            Log::info('Valid APN token found', ['user_id' => $this->user->id]);
        }

        return $tokens;
    }

    protected function validateFcmToken($token): bool
    {
        $isValid = !empty($token) && is_string($token) && 
               preg_match('/^[a-zA-Z0-9_-]+:[a-zA-Z0-9_-]+$/', $token);
        
        if (!$isValid && !empty($token)) {
            Log::warning('Invalid FCM token format', [
                'token' => substr($token, 0, 10).'...',
                'length' => strlen($token)
            ]);
        }
        
        return $isValid;
    }

    protected function validateApnToken($token): bool
    {
        $isValid = !empty($token) && is_string($token) && 
               strlen($token) === 64 && ctype_xdigit($token);
        
        if (!$isValid && !empty($token)) {
            Log::warning('Invalid APN token format', [
                'token' => substr($token, 0, 10).'...',
                'length' => strlen($token)
            ]);
        }
        
        return $isValid;
    }

    protected function sendNotification(FcmService $fcmService, array $tokenData)
    {
        try {
            $message = $this->buildMessage($tokenData);
            
            Log::info('FCM Message Prepared: ', $message);
            Log::info('Sending FCM message to: fcm.googleapis.com/v1/projects/'.env('FIREBASE_PROJECT_ID').'/messages:send');
            Log::info('FCM Message: '.json_encode($message, JSON_UNESCAPED_UNICODE));
            
            if ($this->isTest) {
                $response = $fcmService->sendFcmMessage($message);
                
                Log::info('FCM Response Status: 200');
                Log::info('FCM Response Body: '.json_encode($response, JSON_PRETTY_PRINT));
                Log::info('FCM Response: '.json_encode($response));
            } else {
                FcmPushNotification::dispatch(
                    $this->title,
                    $this->body,
                    [$tokenData['token']],
                    $this->data
                )->onQueue('notifications');
                
                Log::info('Notification dispatched to queue');
            }

        } catch (\Exception $e) {
            Log::error('Failed to send notification', [
                'token_type' => $tokenData['type'],
                'error' => $e->getMessage(),
                'message_data' => $message ?? null
            ]);
            
            if ($this->isTest) {
                throw $e;
            }
        }
    }

    protected function buildMessage(array $tokenData): array
    {
        $message = [
            'message' => [
                'token' => $tokenData['token'],
                'notification' => [
                    'title' => $this->title,
                    'body' => $this->body
                ],
                'data' => $this->data
            ]
        ];

        if ($tokenData['platform'] === 'android') {
            $message['message']['android'] = [
                'priority' => 'high',
                'notification' => [
                    'channel_id' => 'high_importance_channel',
                    'sound' => 'default',
                    'visibility' => 'public',
                    'click_action' => 'FULL_SCREEN_INTENT' 
                ],
                'fcm_options' => [
                    'analytics_label' => 'full_screen_notification'
                ]
            ];
        } else {
            $message['message']['apns'] = [
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                        'badge' => 1
                    ]
                ],
                'headers' => [
                    'apns-priority' => '10'
                ]
            ];
        }

        return $message;
    }

    public function failed(\Exception $exception)
    {
        Log::critical('SendPushNotification job failed', [
            'user_id' => $this->user->id ?? null,
            'error' => $exception->getMessage(),
            'is_test' => $this->isTest,
            'last_data' => [
                'title' => $this->title,
                'body' => $this->body,
                'data' => $this->data
            ]
        ]);
    }
}
