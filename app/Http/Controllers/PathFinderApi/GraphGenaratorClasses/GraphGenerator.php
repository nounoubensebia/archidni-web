<?php

/**
 * Created by PhpStorm.
 * User: ressay
 * Date: 06/10/17
 * Time: 11:43
 */


use App\MetroTrip;
use App\TrainTrip;
include "StationGenerator.php";
include "UtilFunctions.php";
include "TripGenerator.php";
include "GraphLinker.php";
include "GraphClasses/AStar.php";
include "GraphClasses/HeuristicEstimator.php";
include "PathNode.php";

class GraphGenerator
{


    /**
     * $position1 array 0 : latitude, 1 : longitude. starting position
     * $position2 array 0 : latitude, 1 : longitude. destination position
     * @param $params
     * @return array
     */
    public static function generateGraph($params = [])
    {
        $position1 = $params["origin"];
        $position2 = $params["destination"];
        $time = $params["time"];


        $graph = new Graph();
        $origin = new Node("origin");
        $destination = new Node("destination");
        // setting positions
        $origin->addData("position",[$position1["latitude"],$position1["longitude"]]);
        $destination->addData("position",[$position2["latitude"],$position2["longitude"]]);
        // adding nodes to graph
        $graph->addNode($origin);
        $graph->addNode($destination);
        $graph->attachNodes($origin,$destination,UtilFunctions::getTime(
            $origin->getData("position"),$destination->getData("position")
        ))->addData("type","byFoot");

        $stations = StationGenerator::getStationsByFoot($position1);
        $trips = TripGenerator::getTripsFromStations($stations);
        foreach ($trips as $trip) {
            /**@var $trip GraphTrip */
            GraphLinker::linkTripStations($graph,$trip);
            GraphLinker::linkStationsByFoot($graph,$origin,$trip->getStations()
                                            ,GraphLinker::$nToS,$time);
            GraphLinker::linkStationsByFoot($graph,$destination,$trip->getStations()
                                            ,GraphLinker::$sToN,$time);
        }

//        foreach ($graph->getNodes() as $node) {
//            /**@var $node Node */
//            echo "node: ".$node->getTag()." go To: ".count($node->getOEdges())." ";
//
//            foreach ($node->getAttachedNodes() as $attachedNode) {
//                /**@var $attachedNode Node */
//                echo $attachedNode->getTag()." with edge val: ".$node->getWeightTo($attachedNode)." ";
//            }
//            echo "<BR>";
//        }

        $astar = new AStar(new HeuristicEstimatorDijkstra());
        $path = $astar->findPath($origin,$destination);
        $pNodes = PathNode::loadFromPath($path,$time);
        $result = [];
        foreach ($pNodes as $pNode) {
            /** @var $pNode PathNode */
            $result[] = $pNode->toArray();
        }
//        echo "path is: <BR>";
//        foreach ($pNodes as $pNode)
//        {
//            /** @var $pNode PathNode */
//            echo $pNode->getName()." ".$pNode->getWaitingTimeAtNode()." ".$pNode->getTransportModeToNextNode()."<BR>";
//        }
        return $result;
    }




    /**
     * @param $arrival time hh:mm:ss
     * @param $departure time hh:mm:ss
     * @param $edgeVal integer minutes
     * @return mixed
     */
    static function getWaitingTime($arrival,$departure,$edgeVal)
    {
        $arrival = UtilFunctions::strToMin($arrival);
        $departure = UtilFunctions::strToMin($departure);
        return self::getWaitingTimeMins($arrival,$departure,$edgeVal);
    }

    /**
     * @param $arrival integer minutes
     * @param $departure integer minutes
     * @param $edgeVal integer minutes
     * @return mixed
     */

    static function getWaitingTimeMins($arrival,$departure,$edgeVal)
    {
        return $departure - $arrival - $edgeVal;
    }


}