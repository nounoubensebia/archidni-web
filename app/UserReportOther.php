<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserReportOther extends Model
{
    //
    protected $table = 'user_reports_other';

    protected $fillable = ['user_id','description'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
