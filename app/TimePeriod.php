<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TimePeriod extends Model
{
    protected $fillable = ['start','end','waiting_time','metro_trip_id'];

    public $timestamps = false;
    //
    public function metroTrip ()
    {
        return $this->belongsTo('App\MetroTrip');
    }
}
