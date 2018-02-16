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
     * @param $time string format hh:mm
     * @param $day integer bitmask
     * @return array
     */
    public static function getTripsFromStations($stations,$time = "NOW()",$day = 127)
    {
        $result = [];
        $ids = [];
        foreach ($stations as $station) {

            $trips = $station->metroTrips;
            foreach ($trips as $trip) {
                if(!in_array($trip->id,$ids)) {
                    $result[] = GraphTrip::loadFromMetroTrip($trip);
                    $ids[] = $trip->id;
                }
            }

            $trips = $station->trainTrips;
            foreach ($trips as $trip) {
                if(!in_array($trip->id,$ids)) {
                    $result[] = GraphTrip::loadFromTrainTrip($trip);
                    $ids[] = $trip->id;
                }
            }
        }
        return $result;
    }
}