<?php

namespace App\Http\Controllers;

use App\Http\Resources\LineResource;
use App\Line;
use App\Parking;
use Illuminate\Http\Request;

class LinesAndPlacesController extends Controller
{
    //

    public function getAllPlacesAndLines (Request $request)
    {
        $lines = Line::all();
        $data = array();
        $data['lines'] = LineResource::collection($lines);
        $data['parkings'] = Parking::all();
        return $data;
    }
}
