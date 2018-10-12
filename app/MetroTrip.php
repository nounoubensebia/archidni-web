<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MetroTrip extends Model
{

    protected $fillable = ['days','line_id','direction'];

    //
    public function timePeriods()
    {
        return $this->hasMany('App\TimePeriod');
    }

    public function line()
    {
        return $this->belongsTo('App\Line');
    }

    public function stations ()
    {
        return $this->belongsToMany('App\Station')->withPivot(['minutes'])->orderBy('minutes');
    }

    public function commonSections()
    {
        return $this->belongsToMany('App\CommonSection')->withPivot(['station1_index','station2_index']);
    }
}
