<?php
/**
 * Created by PhpStorm.
 * User: noure
 * Date: 07/02/2018
 * Time: 09:07
 */

namespace App;


use App\Http\Controllers\OtpPathFinder\Coordinate;
use App\Http\Controllers\PathFinderApi\Polyline;

class GeoUtils
{
    /**
     * @var int human speed in km/h
     */
    private static $HUMAN_SPEED = 5.0001;

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
        return (double)self::distance($pos1[0],$pos1[1],$pos2[0],$pos2[1])/((double)self::$HUMAN_SPEED/60.000001);
    }

    /**
     * @param $coord1 Coordinate
     * @param $coord2 Coordinate
     * @return float
     */
    public static function getWalkingTimeCoord ($coord1,$coord2)
    {
        return self::distance($coord1->getLatitude(),$coord1->getLongitude(),$coord2->getLatitude(),$coord2->getLongitude());
    }

    public static function getPolylineDuration ($polyline)
    {
        $polylineArr = Polyline::decode($polyline);
        $duration = 0;
        $prevPoint = $polylineArr[0];
        foreach ($polylineArr as $point)
        {
            $duration+=self::getWalkingTime($prevPoint,$point);
            $prevPoint = $point;
        }
        return $duration;
    }
}