<?php

namespace App\Http\Controllers\Api\V1\Driver;

use App\Base\Constants\Auth\Role;
use App\Http\Controllers\Controller;
use App\Models\Admin\Offer;
use App\Models\Admin\OfferDriver;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OffersDriverController extends Controller
{


    protected $offers;
    protected $offer_drivers;

    public function __construct(Offer $offers, OfferDriver $offer_drivers)
    {
        $this->offers = $offers;
        $this->offer_drivers = $offer_drivers;
    }


    public function index()
    {
        $user = auth()->user();

        // Check if the user has the DRIVER role
        if (!$user->hasRole(Role::DRIVER)) {
            return response()->json(['success' => false, 'message' => 'You are not authorized'], 401);
        }

        $driver = $user->driver;
        $current_date = Carbon::now();

        // Get the offers, along with the offer_driver relationship filtered by driver_id
        $data = $this->offers
            ->where('active', true)
            ->where('from_date', '<=', $current_date)
            ->where('to_date', '>=', $current_date)
            ->with(['offer_driver' => function ($query) use ($driver) {
                $query->where('driver_id', $driver->id)->select('offer_id', 'count', 'driver_id'); // Select required columns
            }])
            ->select("*")
            ->get()
            ->makeHidden(['service_location_id']);

        // Iterate over each offer and extract the 'count' field from the offer_driver relationship
        foreach ($data as $offer) {
            $offer->offer_driver_count = $offer->offer_driver->count ?? 0;
            unset($offer->offer_driver);
        }

        // Return the data in the API response
        return $this->respondOk($data);
    }




    private function validate_offers() {}
}
