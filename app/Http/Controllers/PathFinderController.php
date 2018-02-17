<?php

namespace App\Http\Controllers;

use App\Line;
use App\MetroTrip;
use App\Station;
use AStar;
use HeuristicEstimatorDijkstra;
use Illuminate\Http\Request;
use PathNode;

include "PathFinderApi/DataRetrieving/DataRetriever.php";
include "PathFinderApi/GraphGeneratorClasses/PathFinder.php";

class PathFinderController extends Controller
{

    public function findPath()
    {
        $attributes = [];
        if(isset($_GET))
        {
            $attributes = $this->retrieveAttributes($_GET);
        }
        $result = \PathFinder::findPath($attributes);


        return response()->json($result);
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
