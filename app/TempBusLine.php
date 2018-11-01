<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TempBusLine extends Model
{
    //

    protected $fillable = ["number"];

    public function stations()
    {
        return $this->belongsToMany('App\TempBusStation',"temp_bus_line_bus_station",'line_id',
            'station_id')->withPivot(['position','type']);
    }
}
