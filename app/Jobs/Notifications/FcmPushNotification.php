<?php

// namespace App\Jobs\Notifications;

// use Illuminate\Bus\Queueable;
// use Illuminate\Queue\SerializesModels;
// use Illuminate\Queue\InteractsWithQueue;
// use Illuminate\Contracts\Queue\ShouldQueue;
// use Illuminate\Foundation\Bus\Dispatchable;
// use Illuminate\Support\Facades\Log;
// use Google\Client as GoogleClient;
// use Illuminate\Support\Facades\Storage;

// class FcmPushNotification implements ShouldQueue
// {
//     use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

//     protected $title;
//     protected $body;
//     protected $device_token;

//     public function __construct($title, $body, $device_token = null)
//     {
//         $this->title = $title;
//         $this->body = $body;
//         $this->device_token = $device_token;
//     }

//     public function handle()
//     {
//         try {
//             // تحميل إعدادات Firebase
// $projectId = env('FCM_PROJECT_ID');

// $credentialsFilePath = env('FIREBASE_CREDENTIALS');

//             // إنشاء عميل Google
//             $client = new GoogleClient();
//             $client->setAuthConfig($credentialsFilePath);
//             $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
//             $client->refreshTokenWithAssertion();
//             $token = $client->getAccessToken();
//             $accessToken = $token['access_token'];

//             // إعداد رؤوس الطلب
//             $headers = [
//                 "Authorization: Bearer $accessToken",
//                 'Content-Type: application/json'
//             ];

//             // إعداد بيانات الإشعار
//             $data = [
//                 "message" => [
//                     "token" => $this->device_token,
//                     "notification" => [
//                         "title" => $this->title,
//                         "body" => $this->body,
//                     ],
//                 ]
//             ];

//             $payload = json_encode($data);

//             // إرسال الطلب إلى FCM
//             $ch = curl_init();
//             curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
//             curl_setopt($ch, CURLOPT_POST, true);
//             curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//             curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//             curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
//             $response = curl_exec($ch);
//             $err = curl_error($ch);
//             curl_close($ch);

//             // تسجيل النتيجة
//             if ($err) {
//                 Log::error('FCM Error: ' . $err);
//             } else {
//                 Log::info('FCM Notification Sent: ', json_decode($response, true));
//             }
//         } catch (\Exception $e) {
//             Log::error('FCM Job Failed: ' . $e->getMessage());
//         }
//     }
// }

//******************************** New Code ****************************

namespace App\Jobs\Notifications;

use App\Models\User;
use App\Services\FcmService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FcmPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $title;
    protected $body;
    protected $deviceTokens;
    protected $data;

    public function __construct($title, $body, $deviceTokens = null, $data = [])
    {
        $this->title = $title;
        $this->body = $body;
        $this->deviceTokens = $deviceTokens;
        $this->data = $data;
    }



    public function handle(FcmService $fcmService)
    {

        try {
            Log::info('FcmPushNotification: handle() called');

            if (!is_array($this->deviceTokens)) {
                Log::info('Device tokens is not an array, converting to array.');
                $this->deviceTokens = [$this->deviceTokens];
            }

            Log::info('Device tokens: ' . json_encode($this->deviceTokens));
            Log::info('Title: ' . $this->title);
            Log::info('Body: ' . $this->body);
            Log::info('Data: ' . json_encode($this->data));

            foreach ($this->deviceTokens as $deviceToken) {
                Log::info('Sending push notification to device token: ' . $deviceToken);
                $fcmMessage = $fcmService->buildCommonMessage(
                    $deviceToken,
                    $this->title,
                    $this->body,
                    $this->data
                );
                Log::info('FCM Message: ' . json_encode($fcmMessage));
                $result = $fcmService->sendFcmMessage($fcmMessage);
//                return response()->json([
//                    'message' => 'Common message sent to FCM',
//                    'response' => $result
//                ]);
                Log::info('FCM Response: ' . json_encode($result));
            }
        } catch (\Exception $e) {
            Log::error('FCM Push Notification Error: ' . $e->getMessage());
        }
    }
}


