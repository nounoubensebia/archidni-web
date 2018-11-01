<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GeolocStation extends Model
{
    //
    public function locations()
    {
        return $this->hasMany('App\GeolocLocation',"geoloc_station_id");
    }
}
