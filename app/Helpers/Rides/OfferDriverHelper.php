<?php

namespace App\Helpers\Rides;

use App\Base\Constants\Masters\WalletRemarks;
use App\Models\Admin\Offer;
use App\Models\Admin\OfferDriver;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

trait OfferDriverHelper
{

    protected function applyOfferDriver($request_details)
    {


        Log::info('Apply offer Satrt Here');
        Log::info('request Details ' . $request_details);

        // check existing of request
        if (!$request_details || !$request_details->is_completed) return;

        // get all active offers
        $validate_offers  = $this->getValidateOffers();

        if (!$validate_offers) {
            return;
        }


        Log::info('Start Check Offers for driver');
        // start create and add offers driver count
        foreach ($validate_offers as $offer) {
            // check if driver_id exist with the same offer_id
            $offer_driver = OfferDriver::where('driver_id', $request_details->driver_id)
                ->where('offer_id', $offer->id)
                ->first();
            if ($offer_driver &&  $offer_driver->count <  $offer->request_number) {
                Log::info('Increment Count');
                // increment count so that it does not exceed $offer->request_number
                $offer_driver->increment('count');
                if ($offer_driver->count  === $offer->request_number) {
                    Log::info('Increment Driver Wallet');
                    // increment driver wallet amount
                    $this->incrementDriverWallet($offer_driver->driver, $offer->earning_price);
                }
            } else {
                // create new offer driver
                OfferDriver::create([
                    'offer_id' => $offer->id,
                    'driver_id' => $request_details->driver_id,
                    'count' => 1
                ]);
            }
        }
    }


    public function getValidateOffers()
    {


        $current_date = Carbon::today()->toDateTimeString();


        $offers = Offer::where('active', true)
            ->where('from_date', '<=', $current_date)
            ->where('to_date', '>=', $current_date)
            ->get();


        if (count($offers) <= 0) {
            return null;
        }


        return $offers;
    }

    public function incrementDriverWallet($driver, $earning_price)
    {
        // retrive driver wallet
        $driver_wallet = $driver->driverWallet;

        // apply offer earning price to driver wallet
        $driver_wallet->amount_added += $earning_price;
        $driver_wallet->amount_balance += $earning_price;
        $driver_wallet->save();


        // Add the history
        $driver->driverWalletHistory()->create([
            'amount' => $earning_price,
            'transaction_id' => str_random(6),
            'remarks' => WalletRemarks::MONEY_DEPOSITED_TO_E_WALLET_FROM_ADMIN,
            'is_credit' => true,
        ]);
    }
}
