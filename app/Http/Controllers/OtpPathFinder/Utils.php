<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 27/08/2018
 * Time: 18:16
 */

namespace App\Http\Controllers\OtpPathFinder;


use App\GeoUtils;
use App\Http\Controllers\PathFinderApi\Polyline;
use App\MetroTrip;
use App\Place;
use App\Station;
use App\TrainTrip;

class Utils
{
    public static function getTimeFromDateObject ($obj)
    {
        if (isset($obj->year))
        {
            $date = new \DateTime($obj->year."-".$obj->month."-".$obj->dayOfMonth." ".$obj->hourOfDay.":".
                $obj->minute.":".$obj->second);
            return $date->getTimestamp()*1000;
        }
        else
            return $obj;
    }

    public static function strContains($needle, $haystack)
    {
        return strpos($haystack, $needle) !== false;
    }

    public static function getTripDestination($trip)
    {
        $stations = $trip->stations;
        return $stations[count($stations) - 1];
    }

    public static function getRideDuration ($start,$end,$trip)
    {
        $stations = self::getStationsIn($start,$end,$trip);
        if (!isset($stations[0]))
        {
            //TODO remove
            //echo "trip ".$trip->id."start ".$start." "."end ".$end;
            //exit;
            $inter = $end;
            $start = $end;
            $start = $inter;
            $stations = self::getStationsIn($start,$end,$trip);
        }
        $startTime = $stations[0]->pivot->minutes;
        $endTime = $stations[count($stations)-1]->pivot->minutes;
        return $endTime-$startTime;
    }

    public static function getStationsIn ($startId, $endId, $trip)
    {
        $stations = [];
        $allStations = $trip->stations;
        $inside = false;
        foreach ($allStations as $station)
        {
            if ($station->id ==$startId)
            {
                $inside = true;
            }
            if ($inside)
            {
                array_push($stations,$station);
            }
            if ($station->id==$endId)
                break;
        }
        return $stations;
    }

    public static function getFormattedStationsIn ($startId, $endId, $trip)
    {
        $stations = [];
        $allStations = self::getStationsIn($startId,$endId,$trip);
        foreach ($allStations as $station)
        {
            $stationArray = [];
            $stationArray['id'] = $station->id;
            $stationArray['name'] = $station->name;
            $stationArray['coordinate'] = [];
            $stationArray['coordinate']['latitude'] = $station->latitude;
            $stationArray['coordinate']['longitude'] = $station->longitude;
            array_push($stations,$stationArray);
        }
        return $stations;
    }

    public static function getSection ($originId,$destinationId,$acceptReverse,$sections)
    {
        foreach ($sections as $section)
        {
            $condition = $originId == $section->origin_id && $destinationId == $section->destination_id;
            if ($acceptReverse)
            {
                $condition = $condition ||($destinationId == $section->origin_id && $originId == $section->destination_id);
            }
            if ($condition)
            {
                return $section;
            }
        }
        return null;
    }

    public static function decodePolyline($polyline)
    {
        $string = $polyline;
        $byte_array = array_merge(unpack('C*', $string));
        $results = array();

        $index = 0;
        do {
            $shift = 0;
            $result = 0;
            do {
                $char = $byte_array[$index] - 63; # Step 10
                $result |= ($char & 0x1F) << (5 * $shift);
                $shift++;
                $index++;
            } while ($char >= 0x20);
            if ($result & 1)
                $result = ~$result;

            $result = ($result >> 1) * 0.00001;
            $results[] = $result;
        } while ($index < count($byte_array));

        for ($i = 2; $i < count($results); $i++) {
            $results[$i] += $results[$i - 2];
        }

        $results = array_chunk($results, 2);
        $coordinates = array();
        foreach ($results as $coord) {
            $coordinate = array();
            $coordinate['latitude'] = $coord[0];
            $coordinate['longitude'] = $coord[1];
            array_push($coordinates, $coordinate);
        }
        return $coordinates;
    }

    public static function getPolylineFromRideInstruction(PathFinderContext $context, $line, $trip, $stations)
    {

        $stationIds = array();
        foreach ($stations as $station) {
            array_push($stationIds, $station['id']);
        }
        $sections = $line->sections;
        if ($line->transport_mode_id!=2)
        {
            if ($line->transport_mode_id==3)
            {
                $acceptReverse = false;
            }
            else
            {
                $acceptReverse = true;
            }
            $polyline = [];
            for ($i = 0; $i < count($stationIds) - 1;$i++) {
                $section = self::getSection($stationIds[$i],$stationIds[$i+1],$acceptReverse,$sections);
                if (!isset($section))
                {
                    //echo "line ".$line->id." station1 ".$stationIds[$i]." station2 ".$stationIds[$i+1];
                    return "thug";
                }
                $sectionPolyline = $section->polyline;
                $before = Utils::getTimeInMilis();
                $decodedPolyline = self::decodePolyline($sectionPolyline);
                $after = Utils::getTimeInMilis();
                $context->incrementValue("decoding_polyline",($after-$before));
                if ($line->transport_mode_id != 3 && $trip->direction == 1) {
                    $decodedPolyline = array_reverse($decodedPolyline);
                }
                foreach ($decodedPolyline as $coordinate) {

                    array_push($polyline, $coordinate);
                }
            }
            $before = Utils::getTimeInMilis();
            $polylineString = Polyline::encode(Polyline::getPointsFromPolylineArray($polyline));
            $after = Utils::getTimeInMilis();
            $context->incrementValue("encoding_polyline",($after-$before));
            return $polylineString;
        }
        else
        {
            $polyline = [];
            //TODO get Real Polyline

            foreach ($stations as $station)
            {
                array_push($polyline,$station['coordinate']);
            }
            $polylineString = Polyline::encode(Polyline::getPointsFromPolylineObject($polyline));
            return $polylineString;
        }
    }


    public static function getTimeInMilis ()
    {
        return round(microtime(true) * 1000);
    }

    public static function array_unique_multidimensional($input)
    {
        $serialized = array_map('serialize', $input);
        $unique = array_unique($serialized);
        return array_intersect_key($input, $unique);
    }

    public static function getId ($idObj)
    {
        if (isset($idObj->agencyId))
        {
            return $idObj->id;
        }
        else
        {
            $routeId = explode(":",$idObj);
            return $idObj[1];
        }
    }

    public static function getSecondsSinceMidnight ($str_time)
    {
        $str_time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $str_time);
        sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);
        $time_seconds = $hours * 3600 + $minutes * 60 + $seconds;
        return $time_seconds;
    }

    public static function isTripScheduledForDay($trip,$day)
    {
        $days = $trip->day;
        switch ($day)
        {
            case 0://dimanche
                if ($days&1!=0)
                {
                    return true;
                }
                else
                {
                    return false;
                }
                break;
            case 1://lundi
                if ($days&2!=0)
                {
                    return true;
                }
                else
                {
                    return false;
                }
                break;
            case 2://mardi
                if ($days&4!=0)
                {
                    return true;
                }
                else
                {
                    return false;
                }
                break;
            case 3 ://mercredi
                if ($days&8!=0)
                {
                    return true;
                }
                else
                {
                    return false;
                }
                break;
            case  4://jeudi
                if ($days&16!=0)
                {
                    return true;
                }
                else
                {
                    return false;
                }
                break;
            case 5://vendredi
                if ($days&32!=0)
                {
                    return true;
                }
                else
                {
                    return false;
                }
                break;
            case 6://samedi
                if ($days&64!=0)
                {
                    return true;
                }
                else
                {
                    return false;
                }
                break;
        }
    }

    public static function getNearbyPlaces (Coordinate $coordinate)
    {
        $stations = Station::all();
        $places = Place::with("parking","hospital")->get();
        $nearbyPlaces = [];
        $nearbyPlaces['stations'] = [];
        $nearbyPlaces['places'] = [];
        foreach ($stations as $station)
        {
            $distance = GeoUtils::distance($station->latitude,$station->longitude,$coordinate->latitude,
                $coordinate->longitude);
            /*if ($distance<0.5&&!($station->latitude==$coordinate->getLatitude()&&
                $station->longitude==$coordinate->getLongitude()))*/
            if ($distance<0.5)
            {
                array_push($nearbyPlaces['stations'],$station);
            }
        }
        foreach ($places as $place)
        {
            $distance = GeoUtils::distance($place->latitude,$place->longitude,$coordinate->latitude,
                $coordinate->longitude);
            /*if ($distance<0.5&&!($place->latitude==$coordinate->getLatitude()&&
                    $place->longitude==$coordinate->getLongitude()))*/
            if ($distance<0.5)
            {
                array_push($nearbyPlaces['places'],$place);
            }
        }
        return $nearbyPlaces;
    }

}