<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Departure extends Model
{
    //

    public $timestamps = false;

    protected $fillable = ['time'];
    public function trainTrip()
    {
        return $this->belongsTo('App\TrainTrip');
    }
}
