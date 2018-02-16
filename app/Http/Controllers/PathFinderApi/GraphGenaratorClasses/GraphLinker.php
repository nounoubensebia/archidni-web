<?php

/**
 * Created by PhpStorm.
 * User: ressay
 * Date: 10/02/18
 * Time: 19:56
 */

include "GraphClasses/Graph.php";
include "GraphClasses/Node.php";
class GraphLinker
{

    public static $nToS = 1;
    public static $sToN = 2;
    public static $bothWays = 3;

    /**
     * @param $graph Graph
     * @param $node Node
     * @param $stations
     * @param int $mask
     * @param null $time
     * @return Graph
     */
    static public function linkStationsByFoot($graph,$node,$stations,$mask = 3,$time = null)
    {
        if($time == null) $time = UtilFunctions::getCurrentTime();
        foreach ($stations as $station) {
            /** @var $station GraphStation */
            $p1 = $node->getData("position");
            $p2 = [$station->getLatitude(),$station->getLongitude()];
            $edgeVal = UtilFunctions::getTime($p1,$p2);
            $node2 = new Node($station->getTag());
            if($mask & GraphLinker::$sToN)
            $graph->attachNodes($node2,$node
                ,$edgeVal);
            if($mask & GraphLinker::$nToS)
            $graph->attachNodes($node,$node2
                ,$edgeVal + $station->getWaitingTime($time));


        }
        return $graph;
    }

    /**
     * @param $graph Graph
     * @param $trip GraphTrip
     * @return Graph
     */
    static public function linkTripStations($graph,$trip)
    {
        foreach ($trip->getStations() as $station)
        {
            /** @var $station GraphStation */
            $next = $graph->addNode(new Node($station->getTag()));
            $next->addData("station",$station);
            $nextS = $station;
            if(isset($prev) && isset($prevS))
            {
                $graph->attachNodes($prev,$next,$trip->getEdgeValue($prevS,$nextS));
            }
            $prev = $next;
            $prevS = $nextS;
        }
        return $graph;
    }
}