<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    //
    public function lines()
    {
        return $this->belongsToMany('App\Line');
    }

    public function origin()
    {
        return $this->hasOne('App\Station','id','origin_id');
    }

    public function destination()
    {
        return $this->hasOne('App\Station','id','destination_id');
    }
}
