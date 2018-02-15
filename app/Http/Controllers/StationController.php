<?php

namespace App\Http\Controllers;

use App\Http\Resources\StationResource;
use App\Station;
use Illuminate\Http\Request;

class StationController extends Controller
{
    //
    public function getStationAutocompleteSuggestions (Request $request)
    {
        $text = $request->input("text");
        $lines = Station::query()->where("name",'like',"%$text%")
            ->orderByRaw("CASE
        WHEN name like '$text%' THEN 1 WHEN name like '%$text' THEN 3 ELSE 2 END")
        ->get();
        return StationResource::collection($lines);
    }

    public function getStation (Request $request,$id)
    {
        return new StationResource(Station::find($id));
    }
}
