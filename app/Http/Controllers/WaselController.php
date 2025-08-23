<?php

namespace App\Http\Controllers;

use App\Models\Admin\Driver;
use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\WaselTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Models\Request\Request as Triprequest;
use Carbon\Carbon;
use App\Models\wasel_trip;




class WaselController extends Controller
{

    use WaselTrait;



   public function driversIndex()
    {

        $driversIdentityNumbers = Driver::whereNotNull('identityNumber')->pluck('identityNumber');

        // Map the identity numbers to the desired structure
        $driversArray = $driversIdentityNumbers->map(function ($identityNumber) {
            return ['id' => $identityNumber];
        })->toArray();


        $response = $this->getDrivers($driversArray);
   
   		$data = $response['data'];
   
   		
   
   		$page = trans('pages_names.dashboard');
        $main_menu = 'wasel/get-drivers';
        $sub_menu = null;
   
   
   		return view('admin.wasel.index', compact('data', 'page', 'main_menu', 'sub_menu'));
    }


    public function registerDriver($id)
    {
        $page = trans('pages_names.dashboard');
        $main_menu = 'wasel/driver';
        $sub_menu = null;

        $driver = Driver::find($id);

        return view('admin.wasel.driver', compact('page', 'main_menu', 'sub_menu', 'driver'));
    }

    public function storeDriverAndVehicule(Request $request, $id)
    {



        $validated = $request->validate([
            "identityNumber" => 'required',
            "mobileNumber" => "required",
            "dateOfBirthGregorian" => 'required',
            "dateOfBirthHijri" => 'required',
            "sequenceNumber" => 'required',
            "plateLetterRight" => 'required',
            "plateLetterMiddle" => 'required',
            "plateLetterLeft" => 'required',
            "plateNumber" => 'required'
        ]);

        $driver = Driver::find($id);


        $data = [
            "driver" => [
                "identityNumber" => $validated['identityNumber'],
                "dateOfBirthGregorian" => $validated['dateOfBirthGregorian'],
                "dateOfBirthHijri" => $validated['dateOfBirthHijri'],
                "mobileNumber" => $validated['mobileNumber']
            ],
            "vehicle" => [
                "sequenceNumber" => $validated['sequenceNumber'],
                "plateLetterRight" => $validated['plateLetterRight'],
                "plateLetterMiddle" => $validated['plateLetterMiddle'],
                "plateLetterLeft" => $validated['plateLetterLeft'],
                "plateNumber" => $validated['plateNumber'],
                "plateType" => '1',
            ]
        ];


        $response = $this->storeDriver($data);

        if ($response['status'] === true) {

            if ($response['data']['result']['eligibility'] === 'INVALID') {
                return back()->withInput()->withErrors(['error' => "Registration of driver and vehicle failed because of" . json_encode($response['data']['result']['rejectionReasons'])]);
            }

            if ($response['data']['result']['eligibility'] === 'VALID') {
           		 $driver->update([
                	'identityNumber' => $validated['identityNumber'],
            		'sequenceNumber' => $validated['sequenceNumber']
            	]);
                return back()->with(['message' => " Registration of driver and vehicle succeed , Vehicle Expiry Date" . $response['data']['result']['vehicleLicenseExpiryDate']]);
            }
        }


        if ($response['status'] === false) {
            return back()->withInput()->withErrors(['error' =>  "Registration Failed because of " . $response['message']]);
        }
    }


  	public function getTrips()
    {
        $trips = wasel_trip::all();
    	$page = trans('pages_names.dashboard');
        $main_menu = 'wasel/get-trips';
        $sub_menu = null;
   	 return view('admin.wasel.trips', compact('page', 'main_menu', 'sub_menu', 'trips'));
    }


   	public function storeTripe()
    {
    

        $request_trip = Triprequest::where('is_completed', 1)->orderBy('created_at', 'desc')->get();

       	  

        $requestData = $request_trip[0];
    
    
    $ratingFiltered = collect($requestData->requestRating)->filter(function($item) {
    	return $item->user_rating == 1;
    });
    
    $rating =  $ratingFiltered->first()->rating;
    
//     	return $requestData->with(['requestRating' => function($query) {
//         	$query->where('user_rating', 1);
//         }]);
    
     	 $validatedTrip = $this->validateRequestData($requestData);

        if ($validatedTrip['status'] === true) {

            $data = $validatedTrip['data'];

            $response =  $this->registerTrip($data);

            
            if ($response['status'] === true) {
                wasel_trip::create([
                    'request_id' => $requestData->id,
                    'status' => 'VALID'
                ]);
            } else {
                wasel_trip::create([
                    'request_id' => $requestData->id,
                    'status' => 'INVALID',
                    'error_message' => $response['message']
                ]);
            }
        }

    }

}
