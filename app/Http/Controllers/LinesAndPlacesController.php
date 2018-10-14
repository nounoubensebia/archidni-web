<?php

namespace App\Http\Controllers;

use App\Http\Controllers\OtpPathFinder\Utils;
use App\Http\Resources\LineResource;
use App\Line;
use App\Parking;
use App\Place;
use Illuminate\Http\Request;

class LinesAndPlacesController extends Controller
{
    //

    /*public function __construct()
    {
        $this->middleware('token.handler');
    }*/

    public function getAllPlacesAndLines (Request $request)
    {
        $before = Utils::getTimeInMilis();
        //$lines = Line::all();
        $lines = Line::with('sections')->get();
        $data = array();
        $data['lines'] = LineResource::collection($lines);
        $data['places'] = Place::with(["parking","hospital"])->get();
        $after = Utils::getTimeInMilis();
        $data['debug']['time'] = ($after-$before)."";
        return $data;
    }
}
