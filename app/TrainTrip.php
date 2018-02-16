<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrainTrip extends Model
{
    //
    public function line()
    {
        return $this->belongsTo('App\Line');
    }

    public function departures()
    {
        return $this->hasMany('App\Departure');
    }

    public function stations ()
    {
        return $this->belongsToMany('App\Station','train_trip_station')->withPivot('minutes');
    }
}
