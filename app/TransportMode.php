<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransportMode extends Model
{
    //
    public function lines()
    {
        return $this->hasMany('App\Line');
    }

    public function stations()
    {
        return $this->hasMany('App\Station');
    }
}
