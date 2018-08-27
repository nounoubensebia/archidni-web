<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 27/08/2018
 * Time: 18:16
 */

namespace App\Http\Controllers\OtpPathFinder;


use App\Http\Controllers\PathFinderApi\Polyline;
use App\MetroTrip;
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

    public static function getTripDestination($id, $isMetroTrip)
    {
        if ($isMetroTrip) {
            $trip = MetroTrip::find($id);
        } else {
            $trip = TrainTrip::find($id);
        }
        $stations = $trip->stations;
        return $stations[count($stations) - 1];
    }

    public static function getRideDuration ($start,$end,$trip)
    {
        $stations = self::getStationsIn($start,$end,$trip);
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

    public static function getPolylineFromRideInstruction($line,$trip,$stations)
    {

        $stationIds = array();
        foreach ($stations as $station) {
            array_push($stationIds, $station['id']);
        }
        $sections = $line->sections;
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
            $decodedPolyline = self::decodePolyline($sectionPolyline);
            if ($line->transport_mode_id != 3 && $trip->direction == 1) {
                $decodedPolyline = array_reverse($decodedPolyline);
            }
            foreach ($decodedPolyline as $coordinate) {

                array_push($polyline, $coordinate);
            }
        }
        $polylineString = Polyline::encode(Polyline::getPointsFromPolylineArray($polyline));

        return $polylineString;
    }
}