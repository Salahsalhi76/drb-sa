<?php

namespace App\Http\Controllers\Api\V1\Auth;

use Socialite;
use App\Models\User;
use Illuminate\Http\Request;
use App\Base\Constants\Auth\Role;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Auth\SendLoginOTPRequest;
use App\Http\Requests\Auth\App\GenericAppLoginRequest;
use App\Http\Controllers\Web\Auth\LoginController as BaseLoginController;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use App\Models\MobileOtp;
use App\Http\Requests\Auth\Registration\ValidateMobileOTPRequest;
use App\Models\Admin\Driver;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

/**
 * @group Authentication
 *
 * APIs for Authentication
 */
class LoginController extends BaseLoginController
{
    /**
     * Login user and respond with access token and refresh token.
     * @group User-Login
     *
     * @param \App\Http\Requests\Auth\App\GenericAppLoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @bodyParam email string optional email of the user entered
     * @bodyParam mobile string optional mobile of the user entered
     * @bodyParam password string optional password of the user entered
     * @bodyParam device_token string required fcm_token of the user entered

     * @response {
    "token_type": "Bearer",
    "expires_in": 1296000,
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjM4ZTE2N2YyNzlkM2UzZWEzODM5ZGNlMmY4YjdiNDQxYjMwZDQ0YmVlYjAzOWNmZjMzMmE2ZTc0ZDY1MDRiNmE3NjhhZWQzYWU5ZjE5MGUwIn0.eyJhdWQiOiIyIiwianRpIjoiMzhlMTY3ZjI3OWQzZTNlYTM4MzlkY2UyZjhiN2I0NDFiMzBkNDRiZWViMDM5Y2ZmMzMyYTZlNzRkNjUwNGI2YTc2",
    "refresh_token": "def5020045b028faaca5890136e3a8d7c850fb6b95cf2f78698b2356e544ee567cef1efa4099eaea3e3738ba11c9baabb1188a3d49de316e4451f32cdaa6017ebb9ff748fdf43d84b4e796a0456c4125ebaeca7930491fe315e4b86adf787999250966"
}
     */
    public function loginUser(GenericAppLoginRequest $request)
    {

        return $this->loginUserAccountApp($request, Role::USER);
    }

    /**
     * Login driver and respond with access token and refresh token.
     * @group User-Login
     *
     * @param \App\Http\Requests\Auth\App\GenericAppLoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @bodyParam email string optional email of the user entered
     * @bodyParam mobile string optional mobile of the user entered
     * @bodyParam social_unique_id string optional mobile of the user entered
     * @bodyParam password string optional password of the user entered
     * @bodyParam device_token string optional fcm_token for push notification
     * @bodyParam apn_token string optional fcm_token for ios push notification
     * @bodyParam login_by string required i.e android,ios

     * @response {
    "token_type": "Bearer",
    "expires_in": 1296000,
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjM4ZTE2N2YyNzlkM2UzZWEzODM5ZGNlMmY4YjdiNDQxYjMwZDQ0YmVlYjAzOWNmZjMzMmE2ZTc0ZDY1MDRiNmE3NjhhZWQzYWU5ZjE5MGUwIn0.eyJhdWQiOiIyIiwianRpIjoiMzhlMTY3ZjI3OWQzZTNlYTM4MzlkY2UyZjhiN2I0NDFiMzBkNDRiZWViMDM5Y2ZmMzMyYTZlNzRkNjUwNGI2YTc2",
    "refresh_token": "def5020045b028faaca5890136e3a8d7c850fb6b95cf2f78698b2356e544ee567cef1efa4099eaea3e3738ba11c9baabb1188a3d49de316e4451f32cdaa6017ebb9ff748fdf43d84b4e796a0456c4125ebaeca7930491fe315e4b86adf787999250"
}*/
    public function loginDriver(GenericAppLoginRequest $request)
    {

        if ($request->has('role') && $request->role == 'driver') {
            return $this->loginUserAccountApp($request, Role::DRIVER);
        }

        if ($request->has('role') && $request->role == 'owner') {
            return $this->loginUserAccountApp($request, Role::OWNER);
        }

        return $this->loginUserAccountApp($request, Role::DRIVER);
    }


    /**
     * Login Admin user and respond with access token and refresh token.
     * @group User-Login
     *@hideFromAPIDocumentation
     *
     * @param \App\Http\Requests\Auth\App\GenericAppLoginRequest $request
     * @return \Illuminate\Http\JsonResponse

     * @response {
    "token_type": "Bearer",
    "expires_in": 1296000,
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjM4ZTE2N2YyNzlkM2UzZWEzODM5ZGNlMmY4YjdiNDQxYjMwZDQ0YmVlYjAzOWNmZjMzMmE2ZTc0ZDY1MDRiNmE3NjhhZWQzYWU5ZjE5MGUwIn0.eyJhdWQiOiIyIiwiacaP8zkCWTpzh8ZtWBUYVrPkYRWbwz-L5x6dx2d901Aq_7-LwlzPMtP0N93kVfFuLwK2RCzlVtcCTxZaUW9S7x3Y",
    "refresh_token": "def5020045b028faaca5890136e3a8d7c850fb6b95cf2f78698b2356e544ee567cef1efa4099eaea3e3738ba11c9baabb1188a3d49de316e4451f32cdaa6017ebb9ff748fdf43d84b4e796a0456c4125ebaeca7930491fe315e4b86adf7879992509667dd68eacc488bddb2cc005357cdab1da5f0582659eef11e06bf2447c1209f6c17c83453cd6fa6dd6d5d98ff7129a6d3f3509c6c99fba379ea4aee85c0eb89b5f648682484452219d1c592d80c3165657a519f790ba19ad347774c0a199"
}*/
    public function loginAdmin(GenericAppLoginRequest $request)
    {
        return $this->loginUserAccountApp($request, Role::adminRoles());
    }

    /**
     * Social auth
     * @bodyParam device_token string optional fcm_token for push notification
     * @bodyParam login_by string required i.e android,ios
     * @bodyParam oauth_token string required from social provider
     * @return \Illuminate\Http\JsonResponse

     * @response {
    "token_type": "Bearer",
    "expires_in": 1296000,
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjM4ZTE2N2YyNzlkM2UzZWEzODM5ZGNlMmY4YjdiNDQxYjMwZDQ0YmVlYjAzOWNmZjMzMmE2ZTc0ZDY1MDRiNmE3NjhhZWQzYWU5ZjE5MGUwIn0.eyJhdWQiOiIyIiwiacaP8zkCWTpzh8ZtWBUYVrPkYRWbwz-L5x6dx2d901Aq_7-LwlzPMtP0N93kVfFuLwK2RCzlVtcCTxZaUW9S7x3Y",
    "refresh_token": "def5020045b028faaca5890136e3a8d7c850fb6b95cf2f78698b2356e544ee567cef1efa4099eaea3e3738ba11c9baabb1188a3d49de316e4451f32cdaa6017ebb9ff748fdf43d84b4e796a0456c4125ebaeca7930491fe315e4b86adf7879992509667dd68eacc488bddb2cc005357cdab1da5f0582659eef11e06bf2447c1209f6c17c83453cd6fa6dd6d5d98ff7129a6d3f3509c6c99fba379ea4aee85c0eb89b5f648682484452219d1c592d80c3165657a519f790ba19ad347774c0a199"
}*/

    public function socialAuth(Request $request, $provider)
    {
        try {
            $oauth_token = $request->oauth_token;

            // Fetch user from the provider using the token
            $social_user = Socialite::driver($provider)->stateless()
                ->userFromToken($oauth_token);



            if (!$social_user || !$social_user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to retrieve user information from the provider.'
                ], 400);
            }

            // Check if the user exists in the database
            $user = User::where('social_provider', $provider)
                ->where('social_id', $social_user->id)
                ->orWhere('email', $social_user->email)
                ->first();

            Log::info('User ' . $user);

            if (!$user) {

                $email_local_part = explode('@', $social_user->email)[0];

                $user_params = [
                    'name' => $social_user->name ?? $email_local_part,
                    'email' => $social_user->email,
                    'social_id' => $social_user->id,
                    'social_token' => $social_user->token,
                    'social_refresh_token' => $social_user->refreshToken ?? null,
                    'social_expires_in' => $social_user->expiresIn ?? null,
                    'social_avatar' => $social_user->avatar ?? null,
                    'social_avatar_original' => $social_user->avatar_original ?? null,
                    'email_confirmed' => true,
                    'social_provider' => $provider,
                    'country' => 194,
                    'timezone' => 'Asia/Riyadh',
                    'lang' => 'ar',
                    'social_nickname' => $social_user->nickname ?? $email_local_part,
                    'fcm_token' => $request->input('device_token'),
                    'login_by' => $request->input('login_by'),
                ];

                $user = User::create($user_params);

                $user->userWallet()->create(['amount_added' => 0]);

                $user->attachRole(Role::USER);
            } else {
                // Proceed with your logic to update and save the user
                $user->social_id = $social_user->id;
                $user->social_token = $social_user->token;
                $user->social_refresh_token = $social_user->refreshToken ?? null;
                $user->social_expires_in = $social_user->expiresIn ?? null;
                $user->social_avatar = $social_user->avatar ?? null;
                $user->social_avatar_original = $social_user->avatar_original ?? null;
                $user->login_by = $request->input('login_by');
                $user->fcm_token = $request->input('device_token') ?: null;
                $user->save();
            }

            // Issue a new token
            $client_tokens = DB::table('oauth_clients')->where('personal_access_client', 1)->first();

            return $this->issueToken([
                'grant_type' => 'personal_access',
                'client_id' => $client_tokens->id,
                'client_secret' => $client_tokens->secret,
                'user_id' => $user->id,
                'scope' => [],
            ]);
        } catch (\Exception $e) {
            // Catch and return any exception or error that occurred during the OAuth process
            return response()->json([
                'success' => false,
                'message' => 'OAuth login failed: ' . $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Login Dispatcher user and respond with access token and refresh token.
     * @group User-Login
     *
     * @param \App\Http\Requests\Auth\App\GenericAppLoginRequest $request
     * @return \Illuminate\Http\JsonResponse

     * @response {
    "token_type": "Bearer",
    "expires_in": 1296000,
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjM4ZTE2N2YyNzlkM2UzZWEzODM5ZGNlMmY4YjdiNDQxYjMwZDQ0YmVlYjAzOWNmZjMzMmE2ZTc0ZDY1MDRiNmE3NjhhZWQzYWU5ZjE5MGUwIn0.eyJhdWQiOiIyIiwiacaP8zkCWTpzh8ZtWBUYVrPkYRWbwz-L5x6dx2d901Aq_7-LwlzPMtP0N93kVfFuLwK2RCzlVtcCTxZaUW9S7x3Y",
    "refresh_token": "def5020045b028faaca5890136e3a8d7c850fb6b95cf2f78698b2356e544ee567cef1efa4099eaea3e3738ba11c9baabb1188a3d49de316e4451f32cdaa6017ebb9ff748fdf43d84b4e796a0456c4125ebaeca7930491fe315e4b86adf7879992509667dd68eacc488bddb2cc005357cdab1da5f0582659eef11e06bf2447c1209f6c17c83453cd6fa6dd6d5d98ff7129a6d3f3509c6c99fba379ea4aee85c0eb89b5f648682484452219d1c592d80c3165657a519f790ba19ad347774c0a199"
}*/
    public function loginDispatcher(GenericAppLoginRequest $request)
    {
        return $this->loginUserAccountApp($request, Role::DISPATCHER);
    }

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback()
    {
        $user = Socialite::driver('facebook')->user();

        // $user->token;
    }


    /**
     * Logout the user based on their access token.
     * @group User-Login
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @response {"success":true,"message":"success"}
     */
    public function logout(Request $request)
    {
        $user = auth()->user();

        $user->fcm_token = null;
        $user->save();

        auth()->user()->token()->revoke();

        return $this->respondSuccess();
    }

    /**
     * Send the OTP for user login.
     * @group User-Login
     * @param \App\Http\Requests\Auth\SendLoginOTPRequest $request
     * @bodyParam mobile string required mobile of the user entered
     * @return \Illuminate\Http\JsonResponse
     * @response {"success":true,"message":"success","uuid":"54e4ebe54er5e45re5ber54r5r5rr"}
     */
    public function sendUserLoginOTP(SendLoginOTPRequest $request)
    {
        $field = 'mobile';

        $mobile = $request->input($field);

        $user = $this->resolveUserFromMobile($mobile, Role::USER);

        $this->validateUser($user, "رقم الهاتف غير موجود الرجاء التسجيل.", $field);

        if (!$user->createOTP()) {
            $this->throwSendOTPErrorException($field);
        }

        $otp = $user->getCreatedOTP();
        /**
         * Send OTP here
         * Temporary logger
         */
        \Log::info("Login OTP for {$mobile} is : {$otp}");

        return $this->respondSuccess(['uuid' => $user->getCreatedOTPUuid()]);
    }

    /**
     * Validate the user model and their account status.
     *
     * @param \App\Models\User|null $user
     * @param string $message
     * @param string|null $field
     */
    protected function validateUser($user, $message, $field = null)
    {
        if (!$user) {
            $this->throwCustomException($message, $field);
        }

        if (!$user->isActive()) {
            $this->throwAccountDisabledException($field);
        }
    }



    public function mobileOtp(Request $request)
    {
        $mobile = $request->mobile;
        $otp = rand(100000, 999999);

        Log::info("Sms Api Calls");

        // Check if an OTP already exists for the given mobile number
        $existingOtp = MobileOtp::where('mobile', $mobile)->first();

        if ($existingOtp) {
            // Update the existing record with the new OTP
            $existingOtp->otp = $otp;
            $existingOtp->updated_at = now();
            $existingOtp->save();
        } else {
            // Create a new record if no existing record is found
            MobileOtp::create(['mobile' => $mobile, 'otp' => $otp]);
        }

        $active_sms_gateway = get_active_sms_settings();
        Log::info("Active sms gatway" . $active_sms_gateway);

        if (method_exists($this, $method = $active_sms_gateway)) {
            $user = $this->{$method}($mobile, $otp);
        }

        return $this->respondSuccess();
    }

    //twilio
    public function enable_twilio($mobile, $otp)
    {

        $message =  "رمز تحقق درب "  . $otp;

        $username = "966594990027";
        $password = "57051512x";

        $token = "SOp5s6cwCymEAu7aYixsiYlP3wvQhB9KXDkgUCkd";

        $data = [
            "number" => "+966" . $mobile,
            "senderName" => "Mobile.SA",
            "sendAtOption" => "Now",
            "messageBody" => $message,
            "allow_duplicate" => true
        ];

        $url = "https://app.mobile.net.sa/api/v1/send";

        $response = Http::withHeaders([
            "Authorization" => "Bearer " . $token
        ])->post($url, $data);




        if ($response->successful()) {
            return $this->respondSuccess();
        }

        return $this->respondError('Faield to send otp', 500);
    }




    //validate-OTP

 
public function validateSmsOtp(ValidateMobileOTPRequest $request)
    {
        $otp = $request->otp;
        $mobile = $request->mobile;



        $verify_otp = MobileOtp::where('mobile', $mobile)->where('otp', $otp)->first();


        if (!$verify_otp) {
            $this->throwCustomValidationException(['message' => "الرمز المؤقت غير صالح"]);
        }

        $verify_otp->update(['verified' => true]);

        return $this->respondSuccess(['otp' => $verify_otp]);
    }
}
