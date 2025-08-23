<?php

namespace App\Http\Controllers\Web\Admin;

use App\Base\Constants\Auth\Role;
use App\Base\Constants\Masters\PushEnums;
use App\Base\Filters\Master\CommonMasterFilter;
use App\Base\Libraries\QueryFilter\QueryFilterContract;
use App\Base\Services\ImageUploader\ImageUploaderContract;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\BaseController;
use App\Jobs\Notifications\AndroidPushNotification;
use App\Jobs\UserDriverNotificationSaveJob;
use App\Models\Admin\Driver;
use App\Models\Admin\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use App\Jobs\Notifications\SendPushNotification;
use Illuminate\Support\Facades\Log;

class NotificationController extends BaseController
{
    protected $notification;

    protected $imageUploader;
    /**
     * NotificationController constructor.
     *
     * @param \App\Models\Admin\Notification $notification
     */
    public function __construct(Notification $notification, ImageUploaderContract $imageUploader)
    {
        $this->notification = $notification;
        $this->imageUploader = $imageUploader;
    }

    public function index()
    {
        $page = trans('pages_names.push_notification');

        $main_menu = 'notifications';
        $sub_menu = 'push_notification';

        return view('admin.notification.push.index', compact('page', 'main_menu', 'sub_menu'));
    }

    public function fetch(QueryFilterContract $queryFilter)
    {
        $query = $this->notification->query();
        $results = $queryFilter->builder($query)->customFilter(new CommonMasterFilter)->paginate();

        return view('admin.notification.push._pushnotification', compact('results'));
    }

    public function pushView()
    {
        $page = trans('pages_names.push_notification');

        $main_menu = 'notifications';
        $sub_menu = 'push_notification';

        // $users = User::companyKey()->belongsToRole(Role::USER)->active()->get();
   $users = User::paginate(10);
$drivers = Driver::paginate(10);

        if (env('APP_FOR') == 'demo') {
            $drivers = Driver::whereHas('user', function ($query) {
                $query->where('company_key', auth()->user()->company_key);
            })->get();
        }

        return view('admin.notification.push.sendpush', compact('page', 'main_menu', 'sub_menu', 'users', 'drivers'));
    }
    public function sendPush(Request $request)
    {
        $logPrefix = '[Push Notification]';
        $startTime = microtime(true);

        try {
            // 1. بداية العملية
            Log::info("{$logPrefix} Initiate process", [
                'action' => 'start',
                'title' => $request->title,
                'message' => $request->message,
                'target_users' => count($request->user ?? []),
                'target_drivers' => count($request->driver ?? []),
                'has_image' => $request->hasFile('image'),
                'client_ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // 2. التحقق من البيانات الأساسية
            $this->validateRequest($request);

            // 3. إنشاء إشعار في قاعدة البيانات
            $notification = $this->createNotificationRecord($request);
            Log::info("{$logPrefix} Notification record created", [
                'notification_id' => $notification->id,
                'title' => $notification->title,
                'image' => $notification->push_image ?? null
            ]);

            // 4. إعداد بيانات الإشعار الأساسية
            $basePushData = $this->prepareBasePushData($notification);
            Log::debug("{$logPrefix} Base push data prepared", $basePushData);

            // 5. معالجة المستلمين
            $recipientsCount = 0;
            
            if ($request->has('user')) {
                $recipientsCount += $this->processUserRecipients($request, $notification, $basePushData);
            }

            if ($request->has('driver')) {
                $recipientsCount += $this->processDriverRecipients($request, $notification, $basePushData);
            }

            // 6. حفظ سجل الإشعارات
            $this->dispatchNotificationSaveJob($request, $notification);

            // 7. تسجيل نجاح العملية
            $executionTime = round(microtime(true) - $startTime, 3);
            Log::info("{$logPrefix} Process completed successfully", [
                'action' => 'end',
                'status' => 'success',
                'total_recipients' => $recipientsCount,
                'execution_time' => "{$executionTime}s",
                'memory_usage' => memory_get_peak_usage(true) / 1024 / 1024 . "MB"
            ]);

            return redirect('notifications/push')->with('success', 
                trans('success_messages.push_notification_send_successfully'));

        } catch (\Exception $e) {
            // 8. تسجيل الأخطاء
            Log::error("{$logPrefix} Process failed", [
                'action' => 'error',
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'execution_time' => round(microtime(true) - $startTime, 3) . "s"
            ]);
            
            return redirect('notifications/push')->with('error', 
                'Failed to send notifications: ' . $e->getMessage());
        }
    }

    protected function validateRequest($request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'user' => 'sometimes|array',
            'driver' => 'sometimes|array'
        ]);

        if (empty($request->user) && empty($request->driver)) {
            throw new \Exception('At least one recipient (user or driver) must be selected');
        }
    }

    protected function createNotificationRecord($request)
    {
        $params = [
            'title' => $request->title,
            'body' => $request->message,
            'push_enum' => PushEnums::GENERAL_NOTIFICATION,
        ];

        if ($uploadedFile = $this->getValidatedUpload('image', $request)) {
            $params['image'] = $this->imageUploader->file($uploadedFile)
                ->savePushImage();
            Log::info('[Push Notification] Image processed', [
                'image_path' => $params['image'],
                'size' => $uploadedFile->getSize()
            ]);
        }

        return $this->notification->create($params);
    }

    protected function prepareBasePushData($notification)
    {
        return [
            'title' => $notification->title,
            'message' => $notification->body,
            'push_type' => 'general',
            'notification_id' => (string)$notification->id,
            'sent_at' => now()->toDateTimeString(),
            'image' => $notification->push_image ?? null
        ];
    }

    protected function processUserRecipients($request, $notification, $basePushData)
    {
        $notification->update(['for_user' => true]);
        $userIds = $request->user;
        $processed = 0;

        Log::info('[Push Notification] Processing user recipients', [
            'total_users' => count($userIds),
            'sample_user_ids' => array_slice($userIds, 0, 5)
        ]);

        User::whereIn('id', $userIds)->chunk(100, function ($users) use ($basePushData, &$processed) {
            foreach ($users as $user) {
                $this->dispatchNotification($user, $basePushData, 'user');
                $processed++;
            }
        });

        return $processed;
    }

    protected function processDriverRecipients($request, $notification, $basePushData)
    {
        $notification->update(['for_driver' => true]);
        $driverIds = $request->driver;
        $processed = 0;

        Log::info('[Push Notification] Processing driver recipients', [
            'total_drivers' => count($driverIds),
            'sample_driver_ids' => array_slice($driverIds, 0, 5)
        ]);

        Driver::whereIn('id', $driverIds)->with('user')->chunk(100, function ($drivers) use ($basePushData, &$processed) {
            foreach ($drivers as $driver) {
                if ($driver->user) {
                    $this->dispatchNotification($driver->user, $basePushData, 'driver');
                    $processed++;
                }
            }
        });

        return $processed;
    }

    protected function dispatchNotification($user, $pushData, $recipientType)
    {
        try {
            // التحقق من وجود التوكنات
            if (empty($user->fcm_token) && empty($user->apn_token)) {
                Log::warning("No tokens available for {$recipientType}", [
                    'user_id' => $user->id
                ]);
                return;
            }

            // تسجيل معلومات التوكن
            Log::debug("Processing {$recipientType} tokens", [
                'user_id' => $user->id,
                'has_fcm' => !empty($user->fcm_token),
                'has_apn' => !empty($user->apn_token)
            ]);

            // إرسال الإشعار
            dispatch(new SendPushNotification(
                $user,
                $pushData['title'],
                $pushData['message'],
                $pushData
            ))->onQueue('notifications');

            Log::info("Notification dispatched to {$recipientType}", [
                'user_id' => $user->id,
                'notification_id' => $pushData['notification_id']
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to dispatch to {$recipientType}", [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function dispatchNotificationSaveJob($request, $notification)
    {
        dispatch(new UserDriverNotificationSaveJob(
            $request->user ?? [],
            $request->driver ?? [],
            $notification
        ))->onQueue('notifications');

        Log::info('[Push Notification] Dispatched save job', [
            'job' => 'UserDriverNotificationSaveJob',
            'notification_id' => $notification->id
        ]);
    }
    



    
    public function delete(Notification $notification)
    {
        $notification->delete();

        $message = trans('succes_messages.push_notification_deleted_successfully');

        return redirect('notifications/push')->with('success', $message);
    }
    public function getDriversByArea(Request $request)
    {
        $perPage = 5000; // Set the number of records to fetch per page
        $page = $request->input('page', 1); // Get the requested page from the client
        $offset = ($page - 1) * $perPage;

        $drivers = Driver::where('service_location_id', $request->service_location_id)
            ->skip($offset)
            ->take($perPage)
            ->get();

        return response()->json($drivers);
    }
}

