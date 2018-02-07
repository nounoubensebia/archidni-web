<?php

namespace App\Http\Controllers;

use App\Http\Resources\LineResource;
use App\Line;
use Illuminate\Http\Request;


class LineController extends Controller
{
    public function getLinesInsideBoundingBox (Request $request)
    {
        $lines = Line::all();
        $northeast = $request->input('northeast');
        $southwest = $request->input('southwest');
        $arr1 = explode(',',$northeast);
        $arr2 = explode(',',$southwest);
        $maxLat = $arr1[0];
        $maxLng = $arr1[1];
        $minLat = $arr2[0];
        $minLng = $arr2[1];
        $filtered = $lines->reject(function($value,$key) use ($maxLat,$maxLng,$minLat,$minLng){
            $sections = $value->sections;
            foreach ($sections as $section)
            {
                $origin = $section->origin;
                if ($origin->latitude>$minLat&&$origin->latitude<$maxLat&&$origin->longitude<$maxLng&&
                    $origin->longitude>$minLng)
                {
                    return false;
                }
            }
            return true;
        });
        return LineResource::collection($filtered);
    }

    public function getLinesPassingByStation (Request $request,$id)
    {
        $lines = Line::query()->whereHas('sections',function ($query) use ($id) {
           $query->where('origin_id','=',$id)->orWhere('destination_id','=',$id);
        })->get();
        return LineResource::collection($lines);
    }
}
