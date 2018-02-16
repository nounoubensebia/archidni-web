<?php

namespace App\Http\Controllers;

use App\Line;
use App\MetroTrip;
use App\Station;
use Illuminate\Http\Request;
include "PathFinderApi/DataRetrieving/DataRetriever.php";
include "PathFinderApi/GraphGenaratorClasses/GraphGenerator.php";

class PathFinderController extends Controller
{

    public function insertInDatabase()
    {
//        $line = Line::find(1);
//        $metroTrips = $line->metroTrips;
//        foreach ($metroTrips as $metroTrip)
//        {
//            echo $metroTrip."<br>".$metroTrip->timePeriods."<br>";
//            foreach ($metroTrip->stations as $station)
//                echo $station->name."<br>";
//        }

    }

    public function findPath()
    {
        $attributes = [];
        if(isset($_GET))
        {
            $attributes = $this->retrieveAttributes($_GET);
        }
//        var_dump($attributes);
        $path = \GraphGenerator::generateGraph($attributes);

        return response()->json($path);
    }

    private function retrieveAttributes($getAttr)
    {
        $hash = [];
        foreach ($getAttr as $key => $value) {
            if(\DataRetriever::isAnAttribute($key))
                $hash[$key] = \DataRetriever::retrieve($key, $value);
        }
        return $hash;
    }
}
