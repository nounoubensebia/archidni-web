<?php

namespace App\Http\Controllers;

use App\GeoUtils;
use App\Http\Controllers\PathFinderApi\PathRetriever;
use App\Http\Controllers\PathFinderApi\PathsFormatter;
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

class PathFinderController extends Controller
{

    private static $PATHFINDERURL="http://192.168.1.8:8080/path";
    private static $path_finder_data_generator_url="http://192.168.1.8:8080/generatePath";
    public function findPath()
    {
        if (isset($_GET)) {
            $attributes = \DataRetriever::retrieveAttributes($_GET);
        }
        $url = self::$PATHFINDERURL."?origin=".$attributes['origin'][0].",".$attributes['origin'][1]."&destination=".
            $attributes['destination'][0].",".$attributes['destination'][1]."&time=36000";
        $pathJson = file_get_contents($url);
        $root = json_decode($pathJson);
        $paths = $root->formattedPaths;
        $pathsFormatter = new PathsFormatter($paths,false);
        return response()->json($pathsFormatter->formatPaths());
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

}
