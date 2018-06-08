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
        return response()->json($pathsTransformed);*/
        /*for($i=0;$i<10000;$i++)
        {
            $station = Station::find($i);
        }*/
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
            $formattedTransfer['walkingTime'] = $unformattedTransfer->walking_time;
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
            array_push($formattedMetroTrips,$formattedMetroTrip);
        }
        return $formattedMetroTrips;
    }

}
