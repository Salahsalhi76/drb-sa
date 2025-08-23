<?php

namespace App\Models;
use App\Traits\WaselTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class wasel_trip extends Model
{
    use HasFactory, WaselTrait;

    protected $fillable = ['request_id', 'error_message', 'status'];
}
