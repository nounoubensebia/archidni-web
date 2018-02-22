<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Line extends Model
{
    //
    public function operator()
    {
        return $this->belongsTo('App\Operator');
    }

    public function reports()
    {
        return $this->hasMany('App\Report');
    }

    public function sections()
    {
        return $this->belongsToMany('App\Section')->with(['origin','destination'])->withPivot(['order','mode']);
    }

    public function trainTrips()
    {
        return $this->hasMany('App\TrainTrip')->with('departures')->with('stations');
    }

    public function metroTrips()
    {
        return $this->hasMany('App\MetroTrip')->with('timePeriods')->with('stations');
    }

    public function transportMode()
    {
        return $this->belongsTo('App\TransportMode');
    }
}
