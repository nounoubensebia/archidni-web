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
        return $this->hasMany('App\Station');
    }
}
