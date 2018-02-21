<?php

/**
 * Created by PhpStorm.
 * User: ressay
 * Date: 10/02/18
 * Time: 15:55
 */

include "GraphTrip.php";
class TripGenerator
{
    /**
     * @param $stations
     * @param $filter GeneratorFilter
     * @return array
     */
    public static function getTripsFromStations($stations,$filter)
    {
        $time = $filter->getTime();
        $day = $filter->getDay();
        $result = [];
        $ids = [];
        foreach ($stations as $station) {

            $trips = $station->metroTrips;
            foreach ($trips as $trip) {
                if(!in_array($trip->id,$ids) && self::satisfyFilter($trip,$filter)) {
                    $result[] = GraphTrip::loadFromMetroTrip($trip);
                    $ids[] = $trip->id;
                }
            }

            $trips = $station->trainTrips;
            foreach ($trips as $trip) {
                if(!in_array($trip->id,$ids) && self::satisfyFilter($trip,$filter)) {
                    $result[] = GraphTrip::loadFromTrainTrip($trip);
                    $ids[] = $trip->id;
                }
            }
        }
        return $result;
    }

    /**
     * @param $trip
     * @param $filter GeneratorFilter
     * @return bool
     */

    private static function satisfyFilter($trip,$filter)
    {
        return !in_array($trip->line->id,$filter->getUnusedTransportMeans()) && ($trip->days & $filter->getDay());
    }
}