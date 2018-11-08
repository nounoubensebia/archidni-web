<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 01/11/2018
 * Time: 19:07
 */

namespace App\Http\Controllers\BusLinesUpdater;


use App\GeolocLine;
use App\TempBusLine;

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

    public static function getAllerProductionStations ($line)
    {
        return self::getProductionStationsByType($line,0);
    }

    public static function getRetourProductionStations ($line)
    {
        return self::getProductionStationsByType($line,1);
    }

    private static function getProductionStationsByType ($line,$type)
    {
        $sections = $line->sections;
        $stations = [];
        $i = 0;
        foreach ($sections as $section)
        {
            if ($section->pivot->mode == $type)
            {
                if ($i==0)
                    array_push($stations,$section->origin);
                array_push($stations,$section->destination);
                $i++;
            }
        }
        return $stations;
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

    public static function getTempStationLocation ($tempStation)
    {
        $locations = $tempStation->locations;
        if (isset($locations)&&$locations->count()>0)
            return $locations->first();
        else
            return null;
    }

    public static function tempLineExists ($number)
    {
        $lines = TempBusLine::all();
        foreach ($lines as $line)
        {
            if ($line->number==$number)
                return true;
        }
        return false;
    }

    public static function geoLocLineExists ($number)
    {
        $lines = GeolocLine::all();
        foreach ($lines as $line)
        {
            if ($line->number==$number)
                return true;
        }
        return false;
    }

}