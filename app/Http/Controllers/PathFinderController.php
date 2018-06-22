<?php

namespace App\Http\Controllers;

use App\GeoUtils;
use App\Http\Controllers\PathFinderApi\PathRetriever;
use App\Http\Controllers\PathFinderApi\PathUtils;
use App\Line;
use App\MetroTrip;
use App\Station;
use App\StationTransfers;
use App\TrainTrip;
use App\TransportMode;
use AStar;
use HeuristicEstimatorDijkstra;
use Illuminate\Http\Request;
use PathNode;
use PathTransformer;


include "PathFinderApi/DataRetrieving/DataRetriever.php";
include "PathFinderApi/GraphGeneratorClasses/PathFinder.php";
include "PathFinderApi/PathTransformer.php";

class PathFinderController extends Controller
{

    private static $PATHFINDERURL="https://archidni-path-finder-1.herokuapp.com/path";
    private static $path_finder_data_generator_url="https://archidni-path-finder-1.herokuapp.com/generatePath";
    public function findPath()
    {
        /*$attributes = [];
        if (isset($_GET)) {
            $attributes = \DataRetriever::retrieveAttributes($_GET);
        }
        $attributes['MaxWalkingTimePerCorrespondence'] = 20;
        $result = PathRetriever::getAllPaths($attributes,3);
        $pathsTransformed = array();
        foreach ($result as $path) {
            $transformedPath = new PathTransformer($path);
            array_push($pathsTransformed, $transformedPath->getTransformedPath());
        }
        //return response()->json($pathsTransformed);
        return response()->json($result);*/
        if (isset($_GET)) {
            $attributes = \DataRetriever::retrieveAttributes($_GET);
        }
        $url = self::$PATHFINDERURL."?origin=".$attributes['origin'][0].",".$attributes['origin'][1]."&destination=".
            $attributes['destination'][0].",".$attributes['destination'][1]."&time=36000";
        $pathJson = file_get_contents($url);
        $result = $this->springPathTransformer($pathJson);
        //return response()->json($result);
        $pathsTransformed = array();
        foreach ($result as $path) {
            if (count($path)>0)
            $transformedPath = new PathTransformer($path);
            //array_push($pathsTransformed, $transformedPath->getTransformedPath());
            $trPath = $transformedPath->getTransformedPath();
            //print_r($trPath);
            $visitedPolylines = array();
            //TODO re implement this
            foreach ($trPath as &$instruction)
            {
                if (strcmp($instruction['type'],"walk_instruction")==0)
                {
                    $birdPolyline = $instruction['polyline'];
                    //print_r($birdPolyline);
                    $realPolyline = $this->getWalkingPolyline($birdPolyline[0],$birdPolyline[1],$visitedPolylines);
                    //print_r($realPolyline);
                    if (isset($realPolyline))
                        $instruction['polyline'] = $realPolyline;
                    //return json_encode($instruction['polyline']);
                }
            }
            array_push($pathsTransformed,$trPath);
        }
        //$transformedPath = new PathTransformer($path);
        return response()->json($pathsTransformed);
        //return $transformedPath->getTransformedPath();
        //return $result;
        /*for($i=0;$i<10000;$i++)
        {
            $station = Station::find($i);
        }*/
    }

    private function springPathTransformer ($pathJson)
    {
        $jsonObject = json_decode($pathJson);
        $formattedPathsObj = $jsonObject->formattedPaths;
        $phpPaths = array();
        foreach ($formattedPathsObj as $formattedPath)
        {
            $phpPath = array();
            $formattedNodesObj = $formattedPath->formattedNodes;
            $i=0;
            foreach ($formattedNodesObj as $formattedNode)
            {
                $phpNode = array();
                $isStation = false;
                $nodeType = $formattedNode->type;
                if (strcmp($nodeType,"originNode")==0)
                {
                    $phpNode['name']="origin";
                }
                else
                {
                    if (strcmp($nodeType,"destinationNode")==0)
                    {
                        $phpNode['name']="destination";
                    }
                    else
                    {
                        $phpNode['name']=Station::find($formattedNode->stationId)->name;
                        $isStation = true;
                    }
                }
                if ($isStation)
                {
                    if (isset($formattedNode->waitingTime))
                        $phpNode['waitingTime']=$formattedNode->waitingTime;
                    else
                        $phpNode['waitingTime']=0;
                    $phpNode['exactWaitingTime']=false;
                    $phpNode['idLine']=$formattedNode->lineId;
                    $phpNode['idTrip']=$formattedNode->tripId;
                    $phpNode['idStation']=$formattedNode->stationId;
                    if (isset($formattedNodesObj[$i+1])&&isset($formattedNodesObj[$i+1]->timeAtStation))
                        $phpNode['timeToNextNode']=-($formattedNode->timeAtStation-$formattedNodesObj[$i+1]->timeAtStation);
                    else
                        $phpNode['timeToNextNode']=$formattedNodesObj[$i+1]->walkingTime;
                }
                else
                {
                    $phpNode['waitingTime']=0;
                    $phpNode['exactWaitingTime']=null;
                    $phpNode['idLine']=null;
                    $phpNode['idTrip']=null;
                    $phpNode['idStation']=null;
                    if (isset($formattedNodesObj[$i+1]))
                    $phpNode['timeToNextNode']=$formattedNodesObj[$i+1]->walkingTime;
                }
                //$phpNode['walkingTime']=$formattedNode->walkingTime;
                $coordinate = $formattedNode->coordinate;
                $phpNode['latitude']=$coordinate->latitude;
                $phpNode['longitude']=$coordinate->longitude;
                if ($phpNode['name']=='origin')
                {
                    $phpNode['transportModeToNextNode']="byFoot";
                }
                else
                if (isset($formattedNodesObj[$i+1]))
                {
                    if (isset($formattedNodesObj[$i+1]->stationId)&&$formattedNodesObj[$i+1]->tripId==$formattedNode->tripId)
                    {
                        $phpNode['transportModeToNextNode'] = Line::find($formattedNodesObj[$i+1]->lineId)->transportMode->name;
                    }
                    else
                    {
                        $phpNode['transportModeToNextNode']="byFoot";
                    }
                }
                else
                {
                    $phpNode['transportModeToNextNode']=null;
                    $phpNode['waitingTime']=null;
                }
                array_push($phpPath,$phpNode);
                $i++;
            }
            array_push($phpPaths,$phpPath);
        }
        return $phpPaths;
    }

    public function generatePath (Request $request)
    {
        $unformattedMetroTrips = MetroTrip::with('line','stations','timePeriods')->get();
        $unformattedTrainTrips = TrainTrip::with('line','stations','departures')->get();
        $unformattedTransfers = StationTransfers::all();

        $trainTrips = $this->getFormattedTrips($unformattedTrainTrips);
        $metroTrips = $this->getFormattedTrips($unformattedMetroTrips);
        $transfers = $this->getFormattedTransfers($unformattedTransfers);
        $data = array('trainTrips'=>$trainTrips,'metroTrips'=>$metroTrips,'transfers'=>$transfers);
        return response()->json($data);
        $url = self::$path_finder_data_generator_url;
        $content = json_encode($data);

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER,
            array("Content-type: application/json"));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

        $json_response = curl_exec($curl);

        return response()->json($json_response);
    }


    private function getTimeStampFromString ($timeString)
    {
        $times = explode(":",$timeString);
        $hours = $times[0];
        $minutes = $times[1];
        $seconds = $times[2];
        return ($hours*3600+$minutes*60+$seconds);
    }

    private function getFormattedTransfers ($unformattedTransfers)
    {
        $transfers = [];
        foreach ($unformattedTransfers as $unformattedTransfer)
        {
            $formattedTransfer = [];
            $formattedTransfer['station1Id'] = $unformattedTransfer->station_id;
            $formattedTransfer['station2Id'] = $unformattedTransfer->transfer_id;
            $formattedTransfer['walkingTimeDirect'] = $unformattedTransfer->walking_time_bird;
            $formattedTransfer['walkingTimeGoogleMaps'] = $unformattedTransfer->walking_time;
            array_push($transfers,$formattedTransfer);
        }
        return $transfers;
    }

    private function getFormattedTrips ($trips)
    {

        $formattedMetroTrips = array();
        foreach ($trips as $trip)
        {
            $formattedMetroTrip = array('id'=>$trip->id,'lineId'=>$trip->line_id);
            $formattedMetroTrip['stationsTrip']= array();
            foreach ($trip->stations as $station)
            {
                $formattedTripStation = array();
                $formattedTripStation['timeAtStation'] = $station->pivot->minutes;
                $formattedStation = array();
                $formattedStation['id'] = $station->id;
                $formattedStation['coordinate'] = array('latitude'=>$station->latitude,'longitude'=>$station->longitude);
                $formattedStation['transportModeId'] = $station->transport_mode_id;
                $formattedTripStation['station'] = $formattedStation;
                array_push($formattedMetroTrip['stationsTrip'],$formattedTripStation);
            }

            if (isset($trip->timePeriods))
            {
                $formattedTimePeriods = array();
                foreach ($trip->timePeriods as $timePeriod)
                {
                    $formattedTimePeriod = [];
                    $formattedTimePeriod['startTime'] = $this->getTimeStampFromString($timePeriod->start);
                    $formattedTimePeriod['endTime'] = $this->getTimeStampFromString($timePeriod->end);
                    $formattedTimePeriod['averageWaitTime'] = $timePeriod->waiting_time;
                    array_push($formattedTimePeriods,$formattedTimePeriod);

                }
                $formattedMetroTrip['timePeriods'] = $formattedTimePeriods;
            }
            else
            {
                $formattedDepartures = array();
                foreach ($trip->departures as $departure)
                {
                    $formattedDeparture = $this->getTimeStampFromString($departure->time);
                    array_push($formattedDepartures,$formattedDeparture);
                }
                $formattedMetroTrip['departures'] = $formattedDepartures;
            }
            usort($formattedMetroTrip['stationsTrip'],function ($a,$b){
                if ($a['timeAtStation']==$b['timeAtStation'])
                    return 0;
                else
                    return ($a['timeAtStation']<$b['timeAtStation']) ? -1 : 1;
            });
            array_push($formattedMetroTrips,$formattedMetroTrip);
        }
        return $formattedMetroTrips;
    }

    function getWalkingPolyline ($origin,$destination,$alreadyVisitedPolylines)
    {
        $found = false;
        $visitedPolyline = null;
        foreach ($alreadyVisitedPolylines as $alreadyVisitedPolyline)
        {
            if ($alreadyVisitedPolyline['origin']['latitude']==$origin['latitude']&&$alreadyVisitedPolyline['origin']['longitude']
            &&$alreadyVisitedPolyline['destination']['latitude']==$destination['latitude']&&$alreadyVisitedPolyline['destination']['longitude'])
            {
                $found = true;
                $visitedPolyline = $alreadyVisitedPolyline['polyline'];
            }
        }

        if (!$found)
        {
            $url = "https://maps.googleapis.com/maps/api/directions/json?&mode=walking&origin=".$origin['latitude'].",".$origin['longitude'].
                "&destination=".$destination['latitude'].",".$destination['longitude']."&key=AIzaSyBgLesrk8GV1xHQamIKPMCjh5_ury77VJg";
            $response = file_get_contents($url);
            $obj = json_decode($response);
            if (!isset($obj))
                return null;
            $routes = $obj->{'routes'};
            if (!isset($routes))
                return null;
            if (!isset($routes[0]))
            {
                //echo $url;
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
            array_push($alreadyVisitedPolylines,array('polyline'=>$decodedPolyline,'origin'=>$origin,
                'destination'=>$destination));
            return $decodedPolyline;
        }
        else
        {
            return $visitedPolyline;
        }
    }


    function decodePolyline ($polyline)
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

}
