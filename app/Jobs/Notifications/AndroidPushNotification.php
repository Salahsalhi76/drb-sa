<?php

namespace App\Jobs\Notifications;

use Illuminate\Mail\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use NotificationChannels\Fcm\Resources\AndroidConfig;
use NotificationChannels\Fcm\Resources\AndroidNotification;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class AndroidPushNotification extends Notification implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels, Dispatchable;


    protected $title;
    protected $body;
    protected $image;
    protected $data;
    protected $sound = 'general_sound';
    protected $channelId = 'general_channel_id';

    /**
     * Create a new job instance.
     *
     * @param $title,$body,$image,$data
     */
    public function __construct($title, $body, $data = null, $image = null)
    {
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
        $this->image = $image;
    }

    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {


        Log::info('This is the android push notification job');

        $notification = FcmNotification::create()
            ->setTitle($this->title)
            ->setBody($this->body);

        if ($this->image) {
            $notification->setImage($this->image);
        }

        $androidConfig = AndroidConfig::create()
            ->setNotification(
                AndroidNotification::create()
                    ->setTitle($this->title)
                    ->setBody($this->body)
                    ->setSound($this->sound) // Set the sound for Android
                    ->setChannelId($this->channelId) // Set the channel ID for Android
                    ->setImage($this->image)
            );




        $fcmMessage = FcmMessage::create()
            ->setNotification($notification)
            ->setAndroid($androidConfig);

        if ($this->data) {
            $fcmMessage->setData($this->data);
        }

        Log::info('Fcm Message ' . $fcmMessage);

        return $fcmMessage;


        // if ($this->data) {


        //     return FcmMessage::create()
        //         ->setData($this->data)
        //         ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
        //             ->setTitle($this->title)
        //             ->setBody($this->body));
        // } else {
        //     return FcmMessage::create()
        //         ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
        //             ->setTitle($this->title)
        //             ->setBody($this->body)
        //             ->setImage($this->image));
        // }
    }
}
