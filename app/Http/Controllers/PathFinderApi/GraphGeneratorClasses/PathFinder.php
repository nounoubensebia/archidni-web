<?php

/**
 * Created by PhpStorm.
 * User: ressay
 * Date: 17/02/18
 * Time: 21:52
 */

include "GraphGenerator.php";
include "GraphClasses/AStar.php";
include "GraphClasses/HeuristicEstimator.php";
include "PathNode.php";
include "GeneratorFilter.php";

class PathFinder
{
    public static function findPath($attributes)
    {
        if(!isset($attributes["time"])) $time = UtilFunctions::strToMin(UtilFunctions::getCurrentTime()); // default now
        else $time = $attributes["time"];

        if(!isset($params["day"])) $day = UtilFunctions::getCurrentDay(); // default today
        else $day = $params["day"];

        $filter = new GeneratorFilter($attributes["origin"],$attributes["destination"],
            $day,$time);


        $graphInfos = \GraphGenerator::generateGraph($filter);
        $origin = $graphInfos["origin"];
        $destination = $graphInfos["destination"];
        $graph = $graphInfos["graph"];

        // applying A*

        $astar = new AStar(new HeuristicEstimatorDijkstra());
        $path = $astar->findPath($origin,$destination,$graph);

        // loading output
        $pNodes = PathNode::loadFromPath($path,$time);
        $result = [];

        foreach ($pNodes as $pNode) {
            /** @var $pNode PathNode */
            $result[] = $pNode->toArray();
        }

        return $result;
    }
}