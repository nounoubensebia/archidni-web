<?php

namespace App\Http\Controllers;

use App\Http\Controllers\PathFinderApi\PathRetriever;
use App\Line;
use App\MetroTrip;
use App\Station;
use AStar;
use HeuristicEstimatorDijkstra;
use Illuminate\Http\Request;
use PathNode;
use PathTransformer;


include "PathFinderApi/DataRetrieving/DataRetriever.php";
include "PathFinderApi/GraphGeneratorClasses/PathFinder.php";
include "PathFinderApi/PathTransformer.php";

class PathFinderController extends Controller
{

    public function findPath()
    {
        $attributes = [];
        if (isset($_GET)) {
            $attributes = \DataRetriever::retrieveAttributes($_GET);
        }
        $attributes['MaxWalkingTimePerCorrespondence'] = 20;
        $result = PathRetriever::getAllPaths($attributes,3);
        $pathsTransformed = array();
        foreach ($result as $path) {
            $transformedPath = new PathTransformer($path);
            array_push($pathsTransformed, $transformedPath->getTransformedPath());
        }
        return response()->json($pathsTransformed);
    }


}
