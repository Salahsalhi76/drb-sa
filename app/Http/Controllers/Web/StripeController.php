<?php

namespace App\Http\Controllers\Web;

use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Http\Request as ValidatorRequest;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use App\Base\Constants\Masters\PushEnums;
use App\Models\Payment\OwnerWallet;
use App\Models\Payment\OwnerWalletHistory;
use App\Transformers\Payment\OwnerWalletTransformer;
use App\Jobs\Notifications\SendPushNotification;
use App\Models\Payment\UserWalletHistory;
use App\Models\Payment\DriverWalletHistory;
use App\Transformers\Payment\WalletTransformer;
use App\Transformers\Payment\DriverWalletTransformer;
use App\Http\Requests\Payment\AddMoneyToWalletRequest;
use App\Transformers\Payment\UserWalletHistoryTransformer;
use App\Transformers\Payment\DriverWalletHistoryTransformer;
use App\Models\Payment\UserWallet;
use App\Models\Payment\DriverWallet;
use App\Base\Constants\Masters\WalletRemarks;
use App\Jobs\Notifications\AndroidPushNotification;
use App\Base\Constants\Auth\Role;
use Carbon\Carbon;
use App\Models\Request\Request as RequestModel;
use App\Models\User;
use Log;
use Kreait\Firebase\Contract\Database;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Base\Constants\Setting\Settings;

class StripeController extends Controller
{

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function stripe(ValidatorRequest $request)
    {

        // Retrieve URL parameters
        $amount = ($request->input('amount') * 100);
        $payment_for = $request->input('payment_for');
        $user_id = $request->input('user_id');
        $request_id = $request->input('request_id');
        // dd($amount);

        $user = User::find($user_id);

        $currency = $user->countryDetail->currency_code ?? "INR";

        // Pass parameters to the view
        return view('stripe.stripe', compact('amount', 'payment_for', 'currency', 'user_id', 'request_id'));
    }

    public function stripeCheckout(ValidatorRequest $request)
    {


        $id = $request->get('id');

        $data = $this->checkoutMoyasar($id);

        if ($data['status'] === "paid") {
            $amount1 = $data['metadata']['amount'];
            $productname = $data['metadata']['productname'];
            $payment_for = $data['metadata']['payment_for'];
            $currency = $data['metadata']['currency'];
            $user_id = $data['metadata']['user_id'];
            $request_id = $data['metadata']['request_id'];
            $amount = $amount1 / 100;
            $total1 = ($amount1 * 100);

            return redirect()->route('checkout.success', [
                'productname' => $productname,
                'payment_for' => $payment_for,
                'currency' => $currency,
                'amount' => $amount,
                'user_id' => $user_id,
                'request_id' => $request_id
            ]);
        } else {
            return redirect()->route('checkout.failure');
        }
    }

    public function stripeCheckoutSuccess(ValidatorRequest $request)
    {



        $web_booking_value = 0;


        $payment_for = $request->get('payment_for');
        $currency = $request->get('currency');
        $amount = $request->get('amount');
        $user_id = $request->get('user_id');
        $request_id = $request->get('request_id');


        if ($payment_for == "wallet") {

            $request_id = null;

            $user = User::find($user_id);

            if ($user->hasRole('user')) {
                $wallet_model = new UserWallet();
                $wallet_add_history_model = new UserWalletHistory();
                $user_id = $user->id;
            } elseif ($user->hasRole('driver')) {
                $wallet_model = new DriverWallet();
                $wallet_add_history_model = new DriverWalletHistory();
                $user_id = $user->driver->id;
            } else {
                $wallet_model = new OwnerWallet();
                $wallet_add_history_model = new OwnerWalletHistory();
                $user_id = $user->owner->id;
            }

            $user_wallet = $wallet_model::firstOrCreate([
                'user_id' => $user_id
            ]);
            $user_wallet->amount_added += $amount;
            $user_wallet->amount_balance += $amount;
            $user_wallet->save();
            $user_wallet->fresh();

            $wallet_add_history_model::create([
                'user_id' => $user_id,
                'amount' => $amount,
                'transaction_id' => $request->PayerID,
                'remarks' => WalletRemarks::MONEY_DEPOSITED_TO_E_WALLET,
                'is_credit' => true
            ]);


            $title = trans('push_notifications.amount_credited_to_your_wallet_title', [], $user->lang);
            $body = trans('push_notifications.amount_credited_to_your_wallet_body', [], $user->lang);

            dispatch(new SendPushNotification($user, $title, $body));

            if ($user->hasRole(Role::USER)) {
                $result =  fractal($user_wallet, new WalletTransformer);
            } elseif ($user->hasRole(Role::DRIVER)) {
                $result =  fractal($user_wallet, new DriverWalletTransformer);
            } else {
                $result =  fractal($user_wallet, new OwnerWalletTransformer);
            }
        } else {

            $request_id = $request->get('request_id');


            $request_detail = RequestModel::where('id', $request_id)->first();

            $web_booking_value = $request_detail->web_booking;

            $request_detail->update(['is_paid' => true]);

            $driver_commission = $request_detail->requestBill->driver_commision;

            $wallet_model = new DriverWallet();
            $wallet_add_history_model = new DriverWalletHistory();
            $user_id = $request_detail->driver_id;
            /*wallet Modal*/
            $user_wallet = $wallet_model::firstOrCreate([
                'user_id' => $user_id
            ]);
            $user_wallet->amount_added += $amount;
            $user_wallet->amount_balance += $amount;
            $user_wallet->save();
            $user_wallet->fresh();
            /*wallet history*/
            $wallet_add_history_model::create([
                'user_id' => $user_id,
                'amount' => $amount,
                'transaction_id' => $request->PayerID,
                'remarks' => WalletRemarks::MONEY_DEPOSITED_TO_E_WALLET,
                'is_credit' => true
            ]);



            $title = trans('push_notifications.amount_credited_to_your_wallet_title', [], $request_detail->driverDetail->user->lang);
            $body = trans('push_notifications.amount_credited_to_your_wallet_body', [], $request_detail->driverDetail->user->lang);

            dispatch(new SendPushNotification($request_detail->driverDetail->user, $title, $body));
            $this->database->getReference('requests/' . $request_detail->id)->update(['is_paid' => 1]);
        }




        return view('success', ['success'], compact('web_booking_value', 'request_id'));
    }

    public function stripeCheckoutError(ValidatorRequest $request)
    {
        return view('failure', ['failure']);
    }

    public function checkoutMoyasar($id)
    {
        $url = "https://api.moyasar.com/v1/payments/";
        $url .= $id;
        $token = base64_encode("sk_live_LfdTVj5kiAtfiFhiAb2iyhDhvBtjyw457zeJVrmv") . ":";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization:Basic $token"
        ));

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = curl_exec($ch);

        if (curl_errno($ch)) {
            return curl_error($ch);
        }

        curl_close($ch);
        $res = json_decode($responseData, true);
        return $res;
    }
}
