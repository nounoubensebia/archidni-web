<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 18/08/2018
 * Time: 13:50
 */

namespace App\Http\Controllers\OtpPathFinder;


use App\GeoUtils;
use App\Http\Controllers\LineHelper;
use App\Http\Controllers\PathFinderApi\Polyline;
use App\Line;
use App\MetroTrip;
use App\TrainTrip;
use InvalidDataFormatException;
use Tymon\JWTAuth\Utils;

class OtpPathFormatter
{
    private $origin;
    private $destination;
    private $json;

    /**
     * OtpPathFormatter constructor.
     * @param $origin
     * @param $destination
     * @param $json
     */
    public function __construct($origin, $destination, $json)
    {
        $this->origin = $origin;
        $this->destination = $destination;
        $this->json = $json;
    }


    /**
     * OtpPathFormatter constructor.
     * @param $origin
     * @param $destination
     * @param $adjustWalking
     * @param $json
     */


    private function getLatLong($value)
    {
        $hash = [];
        if(preg_match("/(\d+\.\d+),(\d+\.\d+)/",$value,$tab))
            $hash = [(double)$tab[1],(double)$tab[2]];

        return $hash;
    }

    /**
     * OtpPathFormatter constructor.
     * @param $origin
     * @param $destination
     * @param $json
     */



    public function getFormattedPaths ()
    {
        $root = json_decode($this->json);
        $pathResponse = $root->response;
        if (!isset($pathResponse->error))
        {
            $plan = $pathResponse->plan;
            if (isset($plan->itineraries))
                $itineraries = $plan->itineraries;
            else
                $itineraries = $plan->itinerary;
            $paths = [];
            foreach ($itineraries as $itinerary)
            {
                array_push($paths,$this->formatPath($itinerary));
            }
            $plan = [];
            $plan['direct_walking'] = $root->directWalking;
            $plan['paths'] = $paths;
            return $plan;
        }
        else
        {
            return [];
        }
    }

    private function formatPath ($itinerary)
    {
        $legs = $itinerary->legs;
        $instructions = [];
        $i=0;
        foreach ($legs as $leg)
        {
            $mode = $leg->mode;
            if (strcmp($mode,"WALK")==0)
            {
                array_push($instructions,$this->getWalkInstruction($leg));
            }
            else
            {
                if ($i==0)
                {
                    array_push($instructions,$this->generateOriginWalkInstruction($leg));
                }
                array_push($instructions,$this->getWaitInstruction($leg,$itinerary));
                array_push($instructions,$this->getRideInstruction($leg));
                if ($i==count($legs)-1)
                {
                    array_push($instructions,$this->generateDestinationInstruction($leg));
                }
            }
            $i++;
        }
        return $instructions;
    }

    private function getRideDuration ($start,$end,$trip)
    {
        $stations = $this->getStationsIn($start,$end,$trip);
        $startTime = $stations[0]->pivot->minutes;
        $endTime = $stations[count($stations)-1]->pivot->minutes;
        return $endTime-$startTime;
    }

    private function getTimeFromDateObject ($obj)
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

    private function generateOriginWalkInstruction ($rideLeg)
    {
        $instruction = [];
        $instruction['type'] = "walk_instruction";
        $instruction['duration'] = GeoUtils::getWalkingTime($this->origin,[$rideLeg->from->lat,$rideLeg->from->lon]);
        $destinationName = $rideLeg->from->name;
        $instruction['destination'] = $destinationName;
        $instruction['polyline'] = Polyline::encode([$this->origin,[$rideLeg->from->lat,$rideLeg->from->lon]]);
        $instruction['destination_type'] = "station";
        return $instruction;
    }

    private function getWalkInstruction ($leg)
    {
        $instruction = [];
        $instruction['type'] = "walk_instruction";
        $endTime = $leg->endTime;
        $startTime = $leg->startTime;
        $duration = $this->getTimeFromDateObject($endTime)-$this->getTimeFromDateObject($startTime);
        $duration /=1000;
        $duration /=60;
        $duration = (int) $duration;
        $instruction['duration'] = $duration;
        $destinationName = $leg->to->name;
        if (strcmp($destinationName,"Destination")==0)
            $instruction['destination_type'] = "user_destination";
        else
            $instruction['destination_type'] = "station";
        $instruction['destination'] = $destinationName;
        $instruction['polyline'] = $leg->legGeometry->points;
        return $instruction;
    }

    private function generateDestinationInstruction ($rideLeg)
    {
        $instruction = [];
        $instruction['type'] = "walk_instruction";
        $instruction['duration'] = GeoUtils::getWalkingTime($this->destination,[$rideLeg->to->lat,$rideLeg->to->lon]);
        $instruction['polyline'] = Polyline::encode([[$rideLeg->to->lat,$rideLeg->to->lon],$this->destination]);
        $instruction['destination'] = $this->destination;
        $instruction['destination_type'] = "user_destination";
        return $instruction;
    }

    private function getWaitInstruction ($leg,$itinerary)
    {
        $instruction = [];
        $instruction['type'] = "wait_instruction";
        $waitStation = $leg->from;
        $instruction['coordinate'] = ['latitude' => $waitStation->lat, 'longitude' => $waitStation->lon];
        $lines = [];
        $lineArray = [];
        $info = $this->getLineTripInfo($leg);
        $line = $info['line'];
        $trip = $info['trip'];
        $lineArray['id'] = $line->id;
        $lineArray['line_name'] = $line->name;
        $lineArray['transport_mode_id'] = $line->transport_mode_id;
        if (isset($leg->from->arrival))
            $duration = $this->getTimeFromDateObject($leg->from->departure) - $this->getTimeFromDateObject($leg->from->arrival);
        else
        {
            $duration = $this->getTimeFromDateObject($leg->startTime)- $this->getTimeFromDateObject($itinerary->startTime);
        }
        $duration /=1000;
        $duration /=60;
        $duration = (int) $duration;
        $lineArray['duration'] = $duration;
        $lineArray['destination'] = $this->getTripDestination($trip->id,$info['is_metro_trip'])->name;
        $lineArray['exact_waiting_time'] = !$info['is_metro_trip'];
        $lineHelper = new LineHelper($line);
        $lineArray['has_perturbations'] = count($lineHelper->getCurrentAlerts())>0;
        array_push($lines,$lineArray);
        $instruction['lines'] = $lines;
        return $instruction;
    }

    private function getRideInstruction ($leg)
    {
        $instruction = [];
        $instruction['type'] = "ride_instruction";
        $info = $this->getLineTripInfo($leg);
        $line = $info['line'];
        $trip = $info['trip'];
        $instruction ['transport_mode_id'] = $line->transport_mode_id;
        //$startId = explode(":",$leg->from->stopId);
        $startId = $this->getId($leg->from->stopId);
        $endId = $this->getId($leg->to->stopId);
        $instruction['stations'] = $this->getFormattedStationsIn($startId,$endId,$trip);
        $instruction['polyline'] = $this->getPolylineFromRideInstruction($line,$trip,$instruction['stations']);
        $instruction['duration'] = $this->getRideDuration($startId,$endId,$trip);
        $instruction['error_margin'] = 0.2;
        return $instruction;
    }

    private function getPolylineFromRideInstruction($line,$trip,$stations)
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
            $section = $this->getSection($stationIds[$i],$stationIds[$i+1],$acceptReverse,$sections);
            if (!isset($section))
            {
                //echo "line ".$line->id." station1 ".$stationIds[$i]." station2 ".$stationIds[$i+1];
                return "thug";
            }
            $sectionPolyline = $section->polyline;
            $decodedPolyline = $this->decodePolyline($sectionPolyline);
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

    private function decodePolyline($polyline)
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

    private function getSection ($originId,$destinationId,$acceptReverse,$sections)
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

    private function getStationsIn ($startId, $endId, $trip)
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

    private function getFormattedStationsIn ($startId, $endId, $trip)
    {
        $stations = [];
        $allStations = $this->getStationsIn($startId,$endId,$trip);
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

    private function getTripDestination($id, $isMetroTrip)
    {
        if ($isMetroTrip) {
            $trip = MetroTrip::find($id);
        } else {
            $trip = TrainTrip::find($id);
        }
        $stations = $trip->stations;
        return $stations[count($stations) - 1];
    }

    private function getLineTripInfo ($leg)
    {
        $info = [];
        $routeId = $this->getId($leg->routeId);
        $line = Line::find($routeId);
        $tripId = $this->getId($leg->tripId);
        if ($this->strContains("m",$tripId))
        {
            $tripId = substr($tripId,1);
            $trip = MetroTrip::find($tripId);
            $info['is_metro_trip'] = true;
        }
        else
        {
            $tripId = substr($tripId,1);
            $trip = TrainTrip::find($tripId);
            $info['is_metro_trip'] = true;
        }
        $info['line'] = $line;
        $info['trip'] = $trip;
        return $info;
    }

    private function getId ($idObj)
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

    private function strContains($needle, $haystack)
    {
        return strpos($haystack, $needle) !== false;
    }

}