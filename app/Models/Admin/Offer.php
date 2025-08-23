<?php

namespace App\Models\Admin;

use App\Base\Uuid\UuidModel;
use App\Models\Traits\HasActive;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory, UuidModel, HasActive;

    protected $fillable = [
        'service_location_id',
        'subject',
        'request_number',
        'earning_price',
        'user_type',
        'from_date',
        'to_date',
        'active'
    ];


    protected $hidden = [
        'user_type',
        'created_at',
        'updated_at'
    ];


    public function offer_drivers()
    {
        return $this->hasMany(OfferDriver::class, 'offer_id');
    }

    public function offer_driver()
    {
        return $this->hasOne(OfferDriver::class, 'offer_id');
        // ->orderBy('created_at', 'asc');
    }
}
