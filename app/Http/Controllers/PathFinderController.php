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
            $attributes = \DataRetriever::retrieveAttributes($_GET);
        }
        $result = \PathFinder::findPath($attributes);


        return response()->json($result);
    }



}
