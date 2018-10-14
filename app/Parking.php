<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Parking extends Model
{
    //
    public function place()
    {
        return $this->belongsTo('App\Place');
    }
}
