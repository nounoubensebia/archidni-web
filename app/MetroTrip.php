<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MetroTrip extends Model
{
    //
    public function timePeriods()
    {
        return $this->hasMany('App\TimePeriod');
    }

    public function line()
    {
        return $this->belongsTo('App\Line');
    }

    public function stations ()
    {
        return $this->belongsToMany('App\Station','metro_trip_station')->withPivot('minutes');
    }
}
