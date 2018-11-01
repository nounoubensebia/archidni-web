<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TempBusStationLocation extends Model
{
    //
    protected $fillable = ['station_id','latitude','longitude','is_verified','arrival'];
}
