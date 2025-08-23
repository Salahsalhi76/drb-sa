<?php

//namespace App\Services;
//
//use Google\Client as GoogleClient;
//use Google\Exception;
//use Illuminate\Support\Facades\Http;
//
//class FcmService
//{
//    protected $projectId;
//    protected $host;
//    protected $path;
//
//    public function __construct()
//    {
//        $this->projectId = env('FIREBASE_PROJECT_ID');
//        $this->host = 'fcm.googleapis.com';
//        $this->path = '/v1/projects/' . $this->projectId . '/messages:send';
//    }
//
//    /**
//     * Get a valid access token.
//     * @throws Exception
//     */
//    public function getAccessToken()
//    {
//        $client = new GoogleClient();
//        $client->setAuthConfig(storage_path('app/service-account.json'));
//        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
//        $client->refreshTokenWithAssertion();
//        $token = $client->getAccessToken();
//
//        return $token['access_token'];
//    }
//
//    /**
//     * Send HTTP request to FCM with given message.
//     */
//    public function sendFcmMessage($fcmMessage)
//    {
//        $accessToken = $this->getAccessToken();
//
//        $response = Http::withHeaders([
//            'Authorization' => 'Bearer ' . $accessToken,
//            'Content-Type' => 'application/json',
//        ])->post("https://{$this->host}{$this->path}", $fcmMessage);
//
//        return $response->json();
//    }
//
//    /**
//     * Build a common FCM message.
//     */
//    public function buildCommonMessage()
//    {
//        return [
//            'message' => [
//                'token' => 'fFttpHlPRk2METHwFNGGgG:APA91bGG8I4zIas2Zpak585blJTRxGoT3jv1v9TeEGSWd639UUTy8j-w3ON1EqcJLa6e6Fxie5eMXBVqMb9MF-9l1r_ux02QKyq-IFhc8RHtE1L5DuIINFg', // your token
//                'notification' => [
//                    'title' => ' hello', // title of the notification
//                    'body' => 'welcom flatuer.' // body of the notification
//                ],
//                'data' => [
//                    'story_id' => 'story_12345' // custom data field, such as a story ID
//                ]
//            ]
//        ];
//    }
//
//    /**
//     * Build an FCM message with platform-specific overrides.
//     */
//    public function buildOverrideMessage()
//    {
//        $fcmMessage = $this->buildCommonMessage();
//
//        $apnsOverride = [
//            'payload' => [
//                'aps' => [
//                    'badge' => 1
//                ]
//            ],
//            'headers' => [
//                'apns-priority' => '10'
//            ]
//        ];
//
//        $androidOverride = [
//            'notification' => [
//                'click_action' => 'android.intent.action.MAIN'
//            ]
//        ];
//
//        $fcmMessage['message']['android'] = $androidOverride;
//        $fcmMessage['message']['apns'] = $apnsOverride;
//
//        return $fcmMessage;
//    }
//}
//

//******************************************************************** New Code *********************************************************

//
//namespace App\Services;
//
//use Google\Client as GoogleClient;
//use Google\Exception;
//use Illuminate\Support\Facades\Http;
//use Illuminate\Support\Facades\Log;
//
//class FcmService
//{
//    protected $projectId;
//    protected $host;
//    protected $path;
//
//    public function __construct()
//    {
//        $this->projectId = env('FIREBASE_PROJECT_ID');
//        $this->host = 'fcm.googleapis.com';
//        $this->path = '/v1/projects/'.$this->projectId.'/messages:send';
//    }
//
//    /**
//     * Get a valid access token.
//     * @throws Exception
//     */
//    public function getAccessToken()
//    {
//        try {
//            $client = new GoogleClient();
//            $client->setAuthConfig(storage_path('app/service-account.json'));
//            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
//            $client->refreshTokenWithAssertion();
//            $token = $client->getAccessToken();
//
//            return $token['access_token'];
//        } catch (Exception $e) {
//            Log::error("Failed to get FCM access token: ".$e->getMessage());
//            return null;
//        }
//    }
//
//    /**
//     * Send HTTP request to FCM with given message.
//     */
//
//
//    public function sendFcmMessage(array $messagePayload)
//    {
//        $accessToken = $this->getAccessToken();
//        if (!$accessToken) {
//            return ['error' => 'Failed to get access token'];
//        }
//
//        Log::info('Sending FCM Message', ['payload' => $messagePayload]);
//
//        $response = Http::withHeaders([
//            'Authorization' => 'Bearer '.$accessToken,
//            'Content-Type' => 'application/json',
//        ])->withOptions([
//            'verify' => false, // تجاوز التحقق من SSL (غير موصى به للإنتاج)
//        ])->post("https://{$this->host}{$this->path}", $messagePayload);
//
//        Log::info('FCM Response', ['response' => $response->json()]);
//
//        return $response->json();
//    }
//
//    /**
//     * Build a dynamic FCM message.
//     */
//    public function buildMessage(
//        $title,
//        $body,
//        $token = null,
//        $topic = null,
//        $condition = null,
//        $data = [],
//        $image = null
//    ) {
//        $message = [
//            'notification' => [
//                'title' => $title,
//                'body' => $body,
//            ],
//            'data' => $data
//        ];
//
//        if ($image) {
//            $message['notification']['image'] = $image;
//        }
//
//        if ($token) {
//            $message['token'] = $token;
//        } elseif ($topic) {
//            $message['topic'] = $topic;
//        } elseif ($condition) {
//            $message['condition'] = $condition;
//        } else {
//            return ['error' => 'No valid recipient (token, topic, or condition) specified'];
//        }
//
//        return ['message' => $message];
//    }
//}

// ******************************* New code **********************************

namespace App\Services;

use Google\Client as GoogleClient;
use Google\Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    protected $projectId;
    protected $host;
    protected $path;

    public function __construct()
    {
        $this->projectId = env('FCM_PROJECT_ID');
        $this->host = 'fcm.googleapis.com';
        $this->path = '/v1/projects/'.$this->projectId.'/messages:send';
    }

    /**
     * Get a valid access token.
     * @throws Exception
     */

    public function getAccessToken()
    {
        try {
            $client = new GoogleClient();
            $client->setAuthConfig(public_path('push-configurations/firebase.json'));
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->refreshTokenWithAssertion();
            $token = $client->getAccessToken();

            return $token['access_token'];
        } catch (\Exception $e) {
            Log::error('Failed to get access token: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send HTTP request to FCM with given message.
     */
    public function sendFcmMessage($fcmMessage)
    {
        $accessToken = $this->getAccessToken();
        Log::info('Sending fcm message to Firebase: ' . $accessToken);

        if (!$accessToken) {
            Log::error('No access token available, cannot send FCM message.');
            return null;
        }

        try {
            Log::info('Sending FCM message to: ' . $this->host . $this->path);
            Log::info('FCM Message: ' . json_encode($fcmMessage));

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post("https://{$this->host}{$this->path}", $fcmMessage);

            Log::info('FCM Response Status: ' . $response->status());
            Log::info('FCM Response Body: ' . $response->body());

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Failed to send FCM message: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Build a common FCM message.
     */
    public function buildCommonMessage($deviceToken, $title, $body, $data = [])
    {
        return [
            'message' => [
                'token' => $deviceToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body
                ],
                'data' => $data
            ]
        ];
    }


    /**
     * Build an FCM message with platform-specific overrides.
     */
    public function buildOverrideMessage($deviceToken, $title, $body, $data = [])
    {
        $fcmMessage = $this->buildCommonMessage($deviceToken, $title, $body, $data);

        $apnsOverride = [
            'payload' => [
                'aps' => [
                    'badge' => 1
                ]
            ],
            'headers' => [
                'apns-priority' => '10'
            ]
        ];

        $androidOverride = [
            'notification' => [
                'click_action' => 'android.intent.action.MAIN'
            ]
        ];

        $fcmMessage['message']['android'] = $androidOverride;
        $fcmMessage['message']['apns'] = $apnsOverride;

        return $fcmMessage;
    }
}
