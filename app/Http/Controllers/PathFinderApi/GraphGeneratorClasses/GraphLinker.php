<?php

/**
 * Created by PhpStorm.
 * User: ressay
 * Date: 10/02/18
 * Time: 19:56
 */

include "GraphClasses/Graph.php";
include "GraphClasses/Node.php";
include "DynamicTransferEdgeGenerator.php";
class GraphLinker
{

    public static $nToS = 1;
    public static $sToN = 2;
    public static $bothWays = 3;
    public static $byFootPenalty = 2.5;

    /**
     * @param $graph Graph
     * @param $position1
     * @param $position2
     * @return array
     */
    static public function linkOriginDestination($graph,$position1,$position2)
    {
        $origin = new Node("origin");
        $destination = new Node("destination");
        // setting positions
        $origin->addData("position",[$position1[0],$position1[1]]);
        $destination->addData("position",[$position2[0],$position2[1]]);
        // adding nodes to graph

        $graph->addNode($origin);
        $graph->addNode($destination);
        $edgeTime = UtilFunctions::getTime(
            $origin->getData("position"),$destination->getData("position")
        );
        $edge = $graph->attachNodes($origin,$destination,$edgeTime*self::$byFootPenalty);
        $edge->addData("type","byFoot");
        $edge->addData("time",$edgeTime);
        return [$origin,$destination];
    }
    /**
     * @param $graph Graph
     * @param $node Node
     * @param $stations
     * @param int $mask
     * @param $filter GeneratorFilter
     * @return Graph
     */
    static public function linkStationsByFoot($graph,$node,$stations,$mask = 3,$filter)
    {
        $time = $filter->getTime();
        foreach ($stations as $station) {
            /** @var $station GraphStation */
            $p1 = $node->getData("position");
            $p2 = [$station->getLatitude(),$station->getLongitude()];
            $edgeVal = UtilFunctions::getTime($p1,$p2);
            $node2 = new Node($station->getTag());
            if($mask & GraphLinker::$sToN) {
                $edge = $graph->attachNodes($node2, $node
                    , $edgeVal*self::$byFootPenalty);
                $edge->addData("type", "byFoot");
                $edge->addData("time",$edgeVal);
            }
            if($mask & GraphLinker::$nToS) {
                $edge = $graph->attachNodes($node, $node2
                    , $edgeVal*self::$byFootPenalty + $station->getWaitingTime($time + $edgeVal));
                $edge->addData("type", "byFoot");
                $edge->addData("time",$edgeVal + $station->getWaitingTime($time + $edgeVal));
            }

        }
        return $graph;
    }

    /**
     * @param $graph Graph
     * @param $trip GraphTrip
     * @param $filter GeneratorFilter
     * @return Graph
     */
    static public function linkTripStations($graph,$trip,$filter)
    {
        $transportMean = $trip->getTransportMean();
        foreach ($trip->getStations() as $station)
        {
            /** @var $station GraphStation */
            $next = $graph->addNode(new Node($station->getTag()));
            $next->addData("station",$station);
            $next->addData("position",[$station->getLatitude(),$station->getLongitude()]);
            $nextS = $station;
            if(isset($prev) && isset($prevS))
            {
                $edgeVal = $trip->getEdgeValue($prevS,$nextS);
                $edge = $graph->attachNodes($prev,$next,$edgeVal);
                $edge->addData("type",$transportMean);
                $edge->addData("time",$edgeVal);
            }
            $prev = $next;
            $prevS = $nextS;
        }
        return $graph;
    }


    /**
     * @param $graph Graph
     * @param $filter GeneratorFilter
     */
    public static function linkExistingNodesAsTransfer($graph,$filter)
    {
        foreach ($graph->getNodes() as $node1) {
            /** @var $node1 Node */
            $station1 = $node1->getData("station");
            if(isset($station1))
            {
                $node1->addDynamicEdgeLoader(new DynamicTransferEdgeLoader());
            }
        }
        $graph->addDynamicContextUpdater(new DynamicTransferContextUpdater($filter,$graph));
    }
}