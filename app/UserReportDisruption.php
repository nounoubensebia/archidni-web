<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserReportDisruption extends Model
{

    protected $table = 'user_reports_disruptions';

    protected $fillable = ['user_id','line_id','transport_mode_id','description'];

    //
    public function transportMode()
    {
        return $this->belongsTo('App\TransportMode');
    }

    public function line()
    {
        return $this->belongsTo('App\Line');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

}
