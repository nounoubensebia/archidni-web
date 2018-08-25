<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    //
    public function line ()
    {
        return $this->hasOne('App\Line');
    }
}
