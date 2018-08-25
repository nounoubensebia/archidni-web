<?php
/**
 * Created by PhpStorm.
 * User: noure
 * Date: 07/02/2018
 * Time: 09:07
 */

namespace App;


class GeoUtils
{
    /**
     * @var int human speed in km/h
     */
    private static $HUMAN_SPEED = 5;

    /**
     * Calculates the great-circle distance between two points, with
     * the Haversine formula.
     * @param float $latitudeFrom Latitude of start point in [deg decimal]
     * @param float $longitudeFrom Longitude of start point in [deg decimal]
     * @param float $latitudeTo Latitude of target point in [deg decimal]
     * @param float $longitudeTo Longitude of target point in [deg decimal]
     * @param float $earthRadius Mean earth radius in [m]
     * @return float Distance between points in [Km] (same as earthRadius)
     */
    public static function distance(
        $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
    {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return ($angle * $earthRadius)/1000;
    }

    /**
     * @param $pos1
     * @param $pos2
     * @return float|int walking time from pos1 to pos2 in minutes
     */

    public static function getWalkingTime ($pos1,$pos2)
    {
        return self::distance($pos1[0],$pos1[1],$pos2[0],$pos2[1])/(self::$HUMAN_SPEED/60);
    }
}