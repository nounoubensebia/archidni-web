<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 01/11/2018
 * Time: 19:07
 */

namespace App\Http\Controllers\BusLinesUpdater;


class BusLinesUpdaterUtils
{

    public static function getAllerStations ($geolocLine)
    {
        return self::getStationsByType($geolocLine,0);
    }

    public static function getRetourStations ($geolocLine)
    {
        return self::getStationsByType($geolocLine,1);
    }

    private static function getStationsByType($line,$type)
    {
        $stations = $line->stations;
        $results = [];
        foreach ($stations as $station)
        {
            if ($type==$station->pivot->type)
            {
                array_push($results,$station);
            }
        }
        return $results;
    }

    public static function getGeolocStationLocation ($geolocStation)
    {
        $locations = $geolocStation->locations;
        $max = 0;
        $locationToTake = null;
        foreach ($locations as $location)
        {
            if ($location->arrival >= $max)
            {
                $locationToTake = $location;
                $max = $location->arrival;
            }
        }
        return $locationToTake;
    }

}