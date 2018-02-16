<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Departure extends Model
{
    //
    public function trainTrip()
    {
        return $this->belongsTo('App\TrainTrip');
    }
}
