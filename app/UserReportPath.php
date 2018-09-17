<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserReportPath extends Model
{
    //
    protected $table = 'user_reports_paths';

    protected $fillable = ['user_id','description','path_data','is_good'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
