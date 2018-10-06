<?php

namespace App\Http\Controllers;

use App\Place;
use Illuminate\Http\Request;

class ParkingController extends Controller
{
    //
    public function index()
    {
        //
        $parkings = Place::where('place_type_id',1)->with('parking')->get();
        return $parkings;
    }
}
