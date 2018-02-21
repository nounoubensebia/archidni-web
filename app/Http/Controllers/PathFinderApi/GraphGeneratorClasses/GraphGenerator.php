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


class GraphGenerator
{


    /**
     * $position1 array 0 : latitude, 1 : longitude. starting position
     * $position2 array 0 : latitude, 1 : longitude. destination position
     * @param $filter GeneratorFilter
     * @return array
     */
    public static function generateGraph($filter)
    {
        $position1 = $filter->getOrigin();
        $position2 = $filter->getDestination();
        $time = $filter->getTime();
        $day = $filter->getDay();

        // creating graph
        $graph = new Graph();

        $nodes = GraphLinker::linkOriginDestination($graph,$position1,$position2);
        $origin = $nodes[0];
        $destination = $nodes[1];

        // generating stations available by foot
        $stations = StationGenerator::getStationsByFoot($position1);
        // generating trips from stations available by foot
        $trips = TripGenerator::getTripsFromStations($stations,$time,$day);

        // linking trip's stations as nodes in graph
        foreach ($trips as $trip) {
            /**@var $trip GraphTrip */
            GraphLinker::linkTripStations($graph,$trip,$filter);
            GraphLinker::linkStationsByFoot($graph,$origin,$trip->getStations()
                                            ,GraphLinker::$nToS,$filter);
            GraphLinker::linkStationsByFoot($graph,$destination,$trip->getStations()
                                            ,GraphLinker::$sToN,$filter);
        }
        GraphLinker::linkExistingNodesAsTransfer($graph,$filter);


        return [
            "origin" => $origin,
            "destination" => $destination,
            "graph" => $graph];
    }

}