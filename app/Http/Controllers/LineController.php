<?php

namespace App\Http\Controllers;

use App\CompanyNotification;
use App\GeoUtils;
use App\Http\Resources\LineResource;
use App\Line;

use App\Parking;
use Illuminate\Http\Request;


class LineController extends Controller
{


    public function getEtusaLines (Request $request)
    {
        $lines = Line::query()->where('transport_mode_id','=','3')->get();
        return LineResource::collection($lines);
    }

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

    public function getNotifications (Request $request,$id)
    {
        $line = Line::find($id);
        $notificationsWithLines = CompanyNotification::whereHas('lines',function ($query) use ($line, $id) {
            $query->where('line_id','=',$id);
        })->whereRaw('(end_datetime > CURRENT_TIMESTAMP() or end_datetime IS NULL)')
            ->whereRaw('start_datetime < CURRENT_TIMESTAMP()')
            ->get();
        $notificationsWithoutLines = CompanyNotification::where('transport_mode_id','=',$line->transport_mode_id)
            ->whereRaw('(end_datetime > CURRENT_TIMESTAMP()or end_datetime IS NULL)')
            ->whereRaw('start_datetime < CURRENT_TIMESTAMP()')
            ->doesntHave('lines')
            ->get();
        $notificationsArray = $notificationsWithLines->toArray();
        $notificationsArray = array_merge($notificationsArray,$notificationsWithoutLines->toArray());
        return response()->json($notificationsArray,200);
    }
}
