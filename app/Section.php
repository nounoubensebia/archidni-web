<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{

    protected $fillable = ['origin_id','destination_id','polyline','durationPolyline'];

    public $timestamps = false;

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
