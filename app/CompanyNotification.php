<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompanyNotification extends Model
{
    //
    protected $fillable = ['title','type','description','line_id','start_datetime','end_datetime'];
}
