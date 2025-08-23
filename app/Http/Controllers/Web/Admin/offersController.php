<?php

namespace App\Http\Controllers\Web\Admin;

use App\Base\Filters\Master\CommonMasterFilter;
use App\Base\Libraries\QueryFilter\QueryFilterContract;
use App\Http\Controllers\Controller;
use App\Models\Admin\Offer;
use App\Models\Admin\ServiceLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class offersController extends Controller
{
    protected $offers;

    public function __construct(Offer $offers)
    {
        $this->offers = $offers;
    }


    public function index() {
        $page = 'view_offers';

        $main_menu = 'manage-oggers';
        $sub_menu = '';

        return view('admin.offers.index', compact('page', 'main_menu', 'sub_menu'));
    }

    public function fetch(QueryFilterContract $queryFilter) {

        $query = $this->offers->query();

        $results = $queryFilter->builder($query)->customFilter(new CommonMasterFilter)->paginate();

        return view('admin.offers._offers', compact('results'));
    }


    public function create() {
        $page = 'add_offers';
        $cities = ServiceLocation::companyKey()->whereActive(true)->get();
        $main_menu = 'manage-offers';
        $sub_menu = '';

        return view('admin.offers.create', compact('cities', 'page', 'main_menu', 'sub_menu'));

    }


    public function store(Request $request) {

        $validator = Validator::make($request->all(), [
            'service_location_id' => 'required|exists:service_locations,id',
            'request_number' => 'required|integer',
            'from_date' => 'required',
            'to_date' => 'required|after:from',
            'earning_price' => 'required',
            "subject" => 'required',
        ]);


        $created_params = $validator->validated();





        $this->offers->create($created_params);

        $message = 'Offer Created With Success';

        return redirect('/offers')->with('success', $message);


    }


    public function getById(Offer $offer)
    {

        $page = 'edit_offers';
        $cities = ServiceLocation::whereActive(true)->get();
        $main_menu = 'others';
        $sub_menu = 'manage-offers';
        $item = $offer;

        $cities = DB::table('service_locations')
            ->where('service_locations.active', 1)
            ->get();



        return view('admin.offers.update', compact('cities', 'item', 'page', 'main_menu', 'sub_menu'));

    }


    public function update(Request $request, Offer $offer) {
        $validator = Validator::make($request->all(), [
            'service_location_id' => 'required|exists:service_locations,id',
            'request_number' => 'required|integer',
            'from_date' => 'required',
            'to_date' => 'required|after:from_date',
            'earning_price' => 'required',
            "subject" => 'required',
        ]);


        $updated_params = $validator->validated();

        $offer->update($updated_params);

        $message = 'Offer Updated With Success';

        return redirect('/offers')->with('success', $message);

    }


    public function toggleStatus(Offer $offer)
    {
        $status = $offer->isActive() ? false : true;
        $offer->update(['active' => $status]);

        $message = 'Status Changed With Success';
        return redirect('offers')->with('success', $message);
    }



    public function delete(Offer $offer)
    {
        $offer->delete();

        $message = 'Offere Deleted with success';
        return redirect('offers')->with('success', $message);
    }
}
