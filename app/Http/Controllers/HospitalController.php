<?php

namespace App\Http\Controllers;

use App\Place;
use Illuminate\Http\Request;

class HospitalController extends Controller
{
    //

    public function index()
    {
        //
        $hospitals = Place::where('place_type_id',2)->with('hospital')->get();
        return $hospitals;
    }

}
