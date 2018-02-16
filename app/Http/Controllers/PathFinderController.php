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
include "PathFinderApi/GraphGeneratorClasses/GraphGenerator.php";
include "PathFinderApi/GraphGeneratorClasses/GraphClasses/AStar.php";
include "PathFinderApi/GraphGeneratorClasses/GraphClasses/HeuristicEstimator.php";
include "PathFinderApi/GraphGeneratorClasses/PathNode.php";

class PathFinderController extends Controller
{

    public function findPath()
    {
        $attributes = [];
        if(isset($_GET))
        {
            $attributes = $this->retrieveAttributes($_GET);
        }
        $graphInfos = \GraphGenerator::generateGraph($attributes);
        $origin = $graphInfos["origin"];
        $destination = $graphInfos["destination"];

        // applying A*

        $astar = new AStar(new HeuristicEstimatorDijkstra());
        $path = $astar->findPath($origin,$destination);

        // loading output
        $pNodes = PathNode::loadFromPath($path,$attributes["time"]);
        $result = [];
        foreach ($pNodes as $pNode) {
            /** @var $pNode PathNode */
            $result[] = $pNode->toArray();
        }

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
