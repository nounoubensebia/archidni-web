<?php

namespace App\Http\Controllers;

use App\GeoUtils;
use App\Http\Resources\LineResource;
use App\Line;

use App\Parking;
use Illuminate\Http\Request;


class LineController extends Controller
{

    public function getLinesPassingByStation (Request $request,$id)
    {
        $lines = Line::query()->whereHas('sections',function ($query) use ($id) {
           $query->where('origin_id','=',$id)->orWhere('destination_id','=',$id);
        })->get();
        return LineResource::collection($lines);
    }

    public function getLineAutocompleteSuggestions (Request $request)
    {
        $text = $request->input("text");
        $lines = Line::query()->where('name','like',"%$text%")->orderByRaw("CASE
        WHEN name like '$text%' THEN 1 WHEN name like '%$text' THEN 3 ELSE 2 END")
            ->get();
        return LineResource::collection($lines);
    }

    public function getLine (Request $request,$id)
    {
        $line = Line::find($id);
        return new LineResource($line);
    }
}
