<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    //
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function line()
    {
        return $this->belongsTo('App\Line');
    }
}
