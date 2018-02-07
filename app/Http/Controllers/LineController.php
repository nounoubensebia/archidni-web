<?php

namespace App\Http\Controllers;

use App\GeoUtils;
use App\Http\Resources\LineResource;
use App\Line;
use Illuminate\Http\Request;


class LineController extends Controller
{
    public function getLinesCloseToPosition (Request $request)
    {
        $lines = Line::all();
        $position = $request->input('position');
        $arr1 = explode(',',$position);
        $lat = $arr1[0];
        $lng = $arr1[1];
        $filtered = $lines->reject(function($value,$key) use ($lat,$lng){
            $sections = $value->sections;
            foreach ($sections as $section)
            {
                $origin = $section->origin;
                if (GeoUtils::haversineGreatCircleDistance($lat,$lng,$origin->latitude,$origin->longitude)<15)
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
