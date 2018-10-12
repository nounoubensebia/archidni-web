<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    public $timestamps = false;

    protected $fillable = ['aotua_id','latitude','longitude','transport_mode_id','name'];

    //
    public function transportMode()
    {
        return $this->belongsTo('App\TransportMode');
    }

    public function trainTrips()
    {
        return $this->belongsToMany('App\TrainTrip' , 'train_trip_station');
    }

    public function metroTrips()
    {
        return $this->belongsToMany('App\MetroTrip');
    }

    public function transfers ()
    {
        return $this->belongsToMany('App\Station',"station_transfers","transfer_id")->withPivot(["walking_time"]);
    }

}
