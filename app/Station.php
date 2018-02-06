<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    //
    public function transportMode()
    {
        return $this->belongsTo('App\TransportMode');
    }

    public function trainTrips()
    {
        return $this->belongsToMany('App\TrainTrip');
    }
}
