<?php

namespace App\Traits;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;


trait WaselTrait
{

    protected $urlApi = "https://wasl.api.elm.sa/api/dispatching/v2/drivers";
    protected $client_id = "22D1232B-8388-4C1E-911B-70600A445520";
    protected $app_id = "88f93473";
    protected $app_key = "24856a49db33137da5ac222f6d77a302";


    public function storeDriver($data)
    {
        try {
            $client = new Client(['verify' => false]);
            $response = $client->post($this->urlApi, [
                'headers' => [
                    "Content-Type" => 'application/json',
                    "client-id" => $this->client_id,
                    "app-id" => $this->app_id,
                    "app-key" => $this->app_key
                ],
                'json' => $data
            ]);

            $data = json_decode($response->getBody(), true);
            return [
                'status' => true,
                'data' => $data
            ];
        } catch (RequestException $e) {

            $response = $e->getResponse();
            $body = $response->getBody();
            $bodyContents = json_decode($body->getContents(), true);

            $message = "";

            if (isset($bodyContents['resultMsg'])) {
                $message = $bodyContents['resultMsg'];
            } else {
                $message = $bodyContents['resultCode'];
            }


            return [
                'status' => false,
                'message' => $message
            ];
        }
    }


    public function getDrivers($data)
    {
        try {
            $client = new Client(['verify' => false]);


            $response = $client->post("https://wasl.api.elm.sa/api/dispatching/v2/drivers/eligibility", [
                'headers' => [
                    "Content-Type" => 'application/json',
                    "client-id" => $this->client_id,
                    "app-id" => $this->app_id,
                    "app-key" => $this->app_key
                ],
                "json" => [
                    'driverIds' => $data
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            return [
                'status' => true,
                'data' => $data
            ];
        } catch (RequestException $e) {

            $response = $e->getResponse();
            $body = $response->getBody();
            $bodyContents = json_decode($body->getContents(), true);

            $message = "";

            if (isset($bodyContents['resultMsg'])) {
                $message = $bodyContents['resultMsg'];
            } else {
                $message = $bodyContents['resultCode'];
            }


            return [
                'status' => false,
                'message' => $message
            ];
        }
    }


 	public function registerTrip($data)
    {
        try {
            $client = new Client(['verify' => false]);
            $response = $client->post("https://wasl.api.elm.sa/api/dispatching/v2/trips", [
                'headers' => [
                    "Content-Type" => 'application/json',
                    "client-id" => $this->client_id,
                    "app-id" => $this->app_id,
                    "app-key" => $this->app_key
                ],
                'json' => $data
            ]);

            $data = json_decode($response->getBody(), true);
            return [
                'status' => true,
                'data' => $data
            ];
        } catch (RequestException $e) {

            $response = $e->getResponse();
            $body = $response->getBody();
            $bodyContents = json_decode($body->getContents(), true);

            $message = "";
        

            if (isset($bodyContents['resultMsg'])) {
                $message = $bodyContents['resultMsg'];
            } else {
                $message = $bodyContents['resultCode'];
            }


            return [
                'status' => false,
                'message' => $message
            ];
        }
    }

 	public function validateRequestData($requestData)
    {

        $driver = $requestData->driverDetail;

        if ($driver->identityNumber && $driver->sequenceNumber) {
            $data = [
                "sequenceNumber" => "247457710",
                'driverId' => "1075958452",
                "tripId" => $requestData->requestPlace->id,
                "distanceInMeters" => $requestData->total_distance * 1000,
                "durationInSeconds" => $requestData->total_time,
                "customerRating" => $requestData->driver_rated,
                "customerWaitingTimeInSeconds" => Carbon::parse($requestData->arrived_at)->diffInSeconds($requestData->accepted_at),
                "originLatitude" => $requestData->requestPlace->pick_lat,
                "originLongitude" => $requestData->requestPlace->pick_lng,
                "destinationLatitude" => $requestData->requestPlace->drop_lat,
                "destinationLongitude" => $requestData->requestPlace->drop_lng,
                "pickupTimestamp" => Carbon::parse($requestData->trip_start_time)->format('Y-m-d\TH:i:s.v'),
                "dropoffTimestamp" => Carbon::parse($requestData->completed_at)->format('Y-m-d\TH:i:s.v'),
                "startedWhen" => Carbon::parse($requestData->created_at)->format('Y-m-d\TH:i:s.v'),
                "tripCost" => $requestData->requestBill->total_amount
            ];


            return [
                'status' => true,
                'data' => $data
            ];
        }


        return [
            'status' => false
        ];
    }
}
