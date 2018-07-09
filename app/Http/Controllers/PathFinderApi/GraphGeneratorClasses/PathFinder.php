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
        ini_set('max_execution_time', 1000);
        $filter = self::initFilter($attributes);

        $graphInfos = \GraphGenerator::generateGraph($filter);


        $origin = $graphInfos["origin"];
        $destination = $graphInfos["destination"];
        $graph = $graphInfos["graph"];

        // applying A*
        $prevTime = $milliseconds = round(microtime(true) * 1000);
        $astar = new AStar(new HeuristicEstimatorDistance());
        $path = $astar->findPath($origin,$destination,$graph);
        $completeTime = $milliseconds = round(microtime(true) * 1000);
        //echo "AstarTime".($completeTime - $prevTime)."<br>";
        // loading output
        $pNodes = PathNode::loadFromPath($path,$filter->getTime());
        $outPath = [];

        foreach ($pNodes as $pNode) {
            /** @var $pNode PathNode */
            $outPath[] = $pNode->toArray();
        }
        $result[] = $outPath;
        return $result;
    }

    private static function initFilter($attributes)
    {
        if(!isset($attributes["time"])) $time = UtilFunctions::strToMin(UtilFunctions::getCurrentTime()); // default now
        else $time = $attributes["time"];

        if(!isset($params["day"])) $day = UtilFunctions::getCurrentDay(); // default today
        else $day = $params["day"];

        $filter = new GeneratorFilter($attributes["origin"],$attributes["destination"],
            $day,$time);

        if(isset($attributes["transportMeanUnused"]))
            $filter->setUnusedTransportMeans($attributes["transportMeanUnused"]);
        else
            $filter->setUnusedTransportMeans([0]);

        if(isset($attributes["transportLineUnused"]))
            $filter->setUnusedTransportLines($attributes["transportLineUnused"]);
        else
            $filter->setUnusedTransportLines([0]);

        if(isset($attributes["MaxWalkingTimePerCorrespondence"]))
            $filter->setMaxWalkingTimePerCorrespondence($attributes["MaxWalkingTimePerCorrespondence"]);
        else
            $filter->setMaxWalkingTimePerCorrespondence(-1);

        if(isset($attributes["MaxWaitingTimePerCorrespondence"]))
            $filter->setMaxWaitingTimePerCorrespondence($attributes["MaxWaitingTimePerCorrespondence"]);
        else
            $filter->setMaxWaitingTimePerCorrespondence(-1);

        return $filter;
    }
}