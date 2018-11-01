<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GeolocLine extends Model
{
    //
    public function stations ()
    {
        return $this->belongsToMany('App\GeolocStation',"geoloc_bus_lines_geoloc_bus_stations",'geoloc_line_id',
            'geoloc_station_id')->withPivot(['type','position']);
    }
}
