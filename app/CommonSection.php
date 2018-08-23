<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CommonSection extends Model
{
    public $timestamps = false;

    public function station1()
    {
        return $this->hasOne('App\Station', 'foreign_key',"station1_id");
    }

    public function station2()
    {
        return $this->hasOne('App\Station', 'foreign_key',"station2_id");
    }

    public function trainTrips()
    {
        return $this->belongsToMany('App\TrainTrip');
    }

    public function metroTrips()
    {
        return $this->belongsToMany('App\MetroTrip');
    }
}
