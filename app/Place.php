<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    //

    public function Parking ()
    {
        return $this->hasOne('App\Parking');
    }

    public function Hospital()
    {
        return $this->hasOne('App\Hospital');
    }

}
