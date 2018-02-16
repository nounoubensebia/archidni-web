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
     * @param $params
     * @return array
     */
    public static function generateGraph($params = [])
    {
        $position1 = $params["origin"];
        $position2 = $params["destination"];
        $time = $params["time"];

        // creating graph
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


        // generating stations available by foot
        $stations = StationGenerator::getStationsByFoot($position1);
        // generating trips from stations available by foot
        $trips = TripGenerator::getTripsFromStations($stations);

        // linking trip's stations as nodes in graph
        foreach ($trips as $trip) {
            /**@var $trip GraphTrip */
            GraphLinker::linkTripStations($graph,$trip);
            GraphLinker::linkStationsByFoot($graph,$origin,$trip->getStations()
                                            ,GraphLinker::$nToS,$time);
            GraphLinker::linkStationsByFoot($graph,$destination,$trip->getStations()
                                            ,GraphLinker::$sToN,$time);
        }


        return [
            "origin" => $origin,
            "destination" => $destination,
            "graph" => $graph];
    }

}