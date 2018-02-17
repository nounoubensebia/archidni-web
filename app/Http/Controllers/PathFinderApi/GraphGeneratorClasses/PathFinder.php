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

class PathFinder
{
    public static function findPath($attributes)
    {
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

        return $result;
    }
}