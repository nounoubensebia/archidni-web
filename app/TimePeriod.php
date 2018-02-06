<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TimePeriod extends Model
{
    //
    public function metroTrip ()
    {
        return $this->belongsTo('App\MetroTrip');
    }
}
