<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 09/07/2018
 * Time: 20:42
 */

namespace App\Http\Controllers\PathFinderApi;


use App\Line;
use App\MetroTrip;
use App\Station;
use App\TrainTrip;

class PathsFormatter
{
    private $paths;
    private $useGoogleMaps;
    private $calculatedGoogleMapsPolylines;
    private $lines;
    /**
     * PathFormatter constructor.
     * @param $path
     * @param $useGoogleMaps
     */
    public function __construct($paths, $useGoogleMaps)
    {
        $this->paths = $paths;
        $this->useGoogleMaps = $useGoogleMaps;
        $this->calculatedGoogleMapsPolylines = array();
    }


    public function formatPaths ()
    {
        $formattedPaths = array();
        foreach ($this->paths as $path)
        {
            array_push($formattedPaths,$this->getFormattedPath($path));
        }
        return $formattedPaths;
    }

    private function getLinesFromPaths ()
    {
        $trips = array();
        foreach ($this->paths as $path)
        {
            $formattedNodes = $path->formattedNodes;
            foreach ($formattedNodes as $formattedNode)
            {
                if (isset($formattedNode->lineId)&&!in_array($formattedNode->lineId,$trips))
                {
                    array_push($trips,$formattedNode->lineId);
                }
            }
        }
        return $trips;
    }

    private function getFormattedPath($path)
    {
        $formattedPath = array();
        $formattedNodes = $path->formattedNodes;
        $i=0;
        while ($i<count($formattedNodes))
        {
            $currentNode = $formattedNodes[$i];
            $nodeType = $currentNode->type;
            if (strcmp($nodeType,"originNode")==0||strcmp($nodeType,"firstStationInTrip")==0)
            {
                if (strcmp($nodeType,"originNode")==0) {
                    $i++;
                    $origin = $currentNode;
                    $destination = $formattedNodes[$i];
                    $currentNode = $destination;
                }
                else
                {
                    $origin = $formattedNodes[$i-1];
                    $destination = $currentNode;
                }
                $instruction = $this->getWalkInstruction($origin,$destination);
                $instruction['destination_type'] = "station";
                $instruction['destination'] = $this->getStation($destination->stationId)->name;
                array_push($formattedPath,$instruction);
            }
            $nodeType = $currentNode->type;
            if (strcmp($nodeType,"firstStationInTrip")==0)
            {
                array_push($formattedPath,$this->getWaitInstruction($currentNode));
                $i++;
                $rideNodes = array();
                array_push($rideNodes,$currentNode);
                while (strcmp($formattedNodes[$i]->type,"stationInsideTripNode")==0)
                {
                    array_push($rideNodes,$formattedNodes[$i]);
                    $i++;
                }
                array_push($formattedPath,$this->getRideInstruction($rideNodes));
            }
            if (strcmp($nodeType,"destinationNode")==0)
            {
                $instruction = $this->getWalkInstruction($currentNode,$formattedNodes[$i-1]);
                $instruction['destination_type'] = "user_destination";
                $instruction['destination'] = "destination";
                array_push($formattedPath,$instruction);
                break;
            }
        }
        return $formattedPath;
    }

    private function getWaitInstruction ($node)
    {
        $instruction = [];
        $instruction['type'] = "wait_instruction";
        $instruction['duration'] = $node->waitingTime;
        $instruction['exact_waiting_time'] = $node->isExactWaitingTime;
        $instruction['coordinate'] = $node->coordinate;
        return $instruction;
    }

    private function getRideInstruction ($nodes)
    {
        $currentNode = $nodes[0];
        $line = $this->getLine($currentNode->lineId);
        $tripId = $currentNode->tripId;
        $tripType = $nodes[1]->tripType;
        $isMetroTrip = (strcmp($tripType,"metroTrip")==0) ? true : false;
        $instruction = [];
        $instruction['type'] = "ride_instruction";
        $instruction['line_name'] = $line->name;
        $instruction['transport_mode_id'] = $line->transport_mode_id;
        $instruction['destination'] = $this->getTripDestination($tripId,$isMetroTrip)->name;
        $duration = 0;
        $i=0;
        $stations = array();
        $previousTimeAtStation = $currentNode->timeAtStation;
        while ($i<count($nodes))
        {
            $currentNode = $nodes[$i];
            $station = $this->getStation($currentNode->stationId);
            $stationArray = [];
            $stationArray['coordinate'] = $currentNode->coordinate;
            $stationArray['name'] = $station->name;
            $stationArray['id'] = $station->id;
            $duration+=$currentNode->timeAtStation - $previousTimeAtStation;
            array_push($stations,$stationArray);
            $i++;
        }
        $instruction['stations'] = $stations;
        $instruction['polyline'] = $this->getPolylineFromRideInstruction($nodes);
        $instruction['duration'] = $duration;
        return $instruction;
    }

    private function getPolylineFromRideInstruction ($nodes)
    {
        $polyline = [];
        foreach ($nodes as $node)
        {
            array_push($polyline,$node->coordinate);
        }
        return $polyline;
    }

    private function getTripDestination ($id,$isMetroTrip)
    {
        if ($isMetroTrip)
        {
            $trip = MetroTrip::find($id);
        }
        else
        {
            $trip = TrainTrip::find($id);
        }
        $stations = $trip->stations;
        return $stations[count($stations)-1];
    }

    private function getStation ($id)
    {
        return Station::find($id);
    }

    private function getLine ($id)
    {
        return Line::find($id);
    }

    private function getWalkInstruction ($originNode,$destinationNode)
    {
        $instruction = array();
        $polyline = $this->getWalkInstructionPolyline($originNode,$destinationNode);
        $instruction['type'] = "walk_instruction";
        $instruction['polyline'] = $polyline;
        return $instruction;
    }

    private function getWalkInstructionPolyline ($originNode,$destinationNode)
    {
        $coordinate1 = $originNode->coordinate;
        $coordinate2 = $destinationNode->coordinate;
        if ($this->useGoogleMaps)
        {
            $polyline = $this->getGoogleMapsPolyline($coordinate1,$coordinate2);
            if (!isset($polyline))
            {
                $polyline = $this->getDirectPolyline($coordinate1,$coordinate2);
            }
        }
        else
        {
            $polyline = $this->getDirectPolyline($coordinate1,$coordinate2);
        }
        return $polyline;
    }

    private function getDirectPolyline ($coordinate1,$coordinate2)
    {
        $polyline = array();
        array_push($polyline,$coordinate1);
        array_push($polyline,$coordinate2);
        return $polyline;
    }

    private function getGoogleMapsPolyline ($coordinate1,$coordinate2)
    {
        $calculatedPolyline = $this->getGoogleMapsCalculatedPolyline($coordinate1,$coordinate2);
        if (isset($calculatedPolyline))
        {
            return $calculatedPolyline['polyline'];
        }
        else
        {
            $url = "https://maps.googleapis.com/maps/api/directions/json?&mode=walking&origin=".$coordinate1->latitude.",".$coordinate1->longitude.
                "&destination=".$coordinate2->latitude.",".$coordinate2->longitude."&key=AIzaSyBgLesrk8GV1xHQamIKPMCjh5_ury77VJg";
            $response = file_get_contents($url);
            $obj = json_decode($response);
            if (!isset($obj))
                return null;
            $routes = $obj->{'routes'};
            if (!isset($routes))
                return null;
            if (!isset($routes[0]))
            {
                return null;
            }
            $route = $routes[0];
            if (!isset($route))
                return null;
            $overviewPolyline = $route->{'overview_polyline'};
            if (!isset($overviewPolyline))
                return null;
            $points = $overviewPolyline->{'points'};
            if (!isset($points))
                return null;
            $decodedPolyline = $this->decodePolyline($points);
            array_push($this->calculatedGoogleMapsPolylines,array('polyline'=>$decodedPolyline,'origin'=>$coordinate1,
                'destination'=>$coordinate2));
            return $decodedPolyline;
        }
    }

    private function decodePolyline ($polyline)
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
                $shift++; $index++;
            } while ($char >= 0x20);
            if ($result & 1)
                $result = ~$result;

            $result = ($result >> 1) * 0.00001;
            $results[] = $result;
        } while ($index < count($byte_array));

        for ($i = 2; $i < count($results); $i++) {
            $results[$i] += $results[$i - 2];
        }

        $results =  array_chunk($results, 2);
        $coordinates = array();
        foreach ($results as $coord)
        {
            $coordinate = array();
            $coordinate['latitude'] = $coord[0];
            $coordinate['longitude'] = $coord[1];
            array_push($coordinates,$coordinate);
        }
        return $coordinates;
    }

    private function getGoogleMapsCalculatedPolyline ($coordinate1, $coordinate2)
    {
        foreach ($this->calculatedGoogleMapsPolylines as $calculatedGoogleMapsPolyline)
        {
            if (($calculatedGoogleMapsPolyline['origin']==$coordinate1&&
                $calculatedGoogleMapsPolyline['destination']==$coordinate2)||
                ($calculatedGoogleMapsPolyline['origin']==$coordinate2&&
                    $calculatedGoogleMapsPolyline['destination']==$coordinate1))
            {
                return $calculatedGoogleMapsPolyline;
            }
        }
        return null;
    }

}