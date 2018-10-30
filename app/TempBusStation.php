<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TempBusStation extends Model
{
    protected $fillable = ['name','aotua_id'];
    //
    public function locations()
    {
        return $this->hasMany('App\TempBusStationLocation',"station_id");
    }
}
