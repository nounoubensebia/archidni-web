<?php

namespace App\Http\Controllers;

use App\Http\Resources\LineResource;
use App\Http\Resources\StationResource;
use App\Line;
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

    public function getTransfersTest (Request $request)
    {
        //use this to get transfers
        $stations = Station::with("transfers")->get();

        return response()->json($stations[200]->transfers,200);
    }

    public function getTransfers (Request $request,$id)
    {
        $station = Station::find($id);
        $transfers = $station->transfers;
        foreach ($transfers as $transfer)
        {
            $transfer['lines'] = $this->getLines($transfer['id']);
            unset($transfer['pivot']);
        }

        return response()->json($transfers,200);
    }

    public function getLinesPassingByStation (Request $request,$id)
    {
        return $this->getLines($id);
    }

    private function getLines ($id)
    {
        $lines = Line::query()->whereHas('sections',function ($query) use ($id) {
            $query->where('origin_id','=',$id)->orWhere('destination_id','=',$id);
        })->get();
        return LineResource::collection($lines);
    }
}
