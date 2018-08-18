<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 18/08/2018
 * Time: 13:50
 */

namespace App\Http\Controllers\PathFinderApi;


use App\Http\Controllers\LineHelper;
use App\Line;
use App\MetroTrip;
use App\TrainTrip;

class OtpPathFormatter
{
    private $json;

    /**
     * OtpPathFormatter constructor.
     * @param $json
     */
    public function __construct($json)
    {
        $this->json = $json;
    }

    public function getFormattedPaths ()
    {
        $root = json_decode($this->json);
        if (!isset($root->error))
        {
            $plan = $root->plan;
            $itineraries = $plan->itineraries;
            $paths = [];
            foreach ($itineraries as $itinerary)
            {
                array_push($paths,$this->formatPath($itinerary));
            }
            return $paths;
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
        foreach ($legs as $leg)
        {
            $mode = $leg->mode;
            if (strcmp($mode,"WALK")==0)
            {
                array_push($instructions,$this->getWalkInstruction($leg));
            }
            else
            {
                array_push($instructions,$this->getWaitInstruction($leg));
                array_push($instructions,$this->getRideInstruction($leg));
            }
        }
        return $instructions;
    }

    private function getWalkInstruction ($leg)
    {
        $instruction = [];
        $instruction['type'] = "walk_instruction";
        $endTime = $leg->endTime;
        $startTime = $leg->startTime;
        $duration = $endTime-$startTime;
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

    private function getWaitInstruction ($leg)
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
        $duration = $leg->from->departure - $leg->from->arrival;
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
        $startId = explode(":",$leg->from->stopId);
        $startId = $startId[1];
        $endId = explode(":",$leg->to->stopId);
        $endId = $endId[1];
        $instruction['stations'] = $this->getStationsIn($startId,$endId,$trip);
        $instruction['polyline'] = $this->getPolylineFromRideInstruction($line,$trip,$instruction['stations']);
        $instruction['duration'] = $leg->duration/60;
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

    private function getStationsIn ($startId,$endId,$trip)
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
                $stationArray = [];
                $stationArray['id'] = $station->id;
                $stationArray['name'] = $station->name;
                $stationArray['coordinate'] = [];
                $stationArray['coordinate']['latitude'] = $station->latitude;
                $stationArray['coordinate']['longitude'] = $station->longitude;
                array_push($stations,$stationArray);
            }
            if ($station->id==$endId)
                break;
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
        $routeId = $leg->routeId;
        $routeId = explode(":",$routeId);
        $line = Line::find($routeId[1]);
        $tripId = $leg->tripId;
        $tripId = explode(":",$tripId);
        if ($this->strContains("m",$tripId[1]))
        {
            $tripId = substr($tripId[1],1);
            $trip = MetroTrip::find($tripId);
            $info['is_metro_trip'] = true;
        }
        else
        {
            $tripId = substr($tripId[1],1);
            $trip = TrainTrip::find($tripId);
            $info['is_metro_trip'] = true;
        }
        $info['line'] = $line;
        $info['trip'] = $trip;
        return $info;
    }



    private function strContains($needle, $haystack)
    {
        return strpos($haystack, $needle) !== false;
    }

}