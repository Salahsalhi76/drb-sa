<?php

namespace App\Models\Admin;

use App\Base\Uuid\UuidModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferDriver extends Model
{
    use HasFactory, UuidModel;

    protected $fillable = [
        'offer_id',
        'driver_id',
        'count'
    ];


    public function offer() {
        return $this->belongsTo(offer::class, 'offer_id');
    }


    public function driver() {
        return $this->belongsTo(Driver::class, 'driver_id');
    }
}
