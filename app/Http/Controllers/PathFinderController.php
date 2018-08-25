<?php

namespace App\Http\Controllers;

use App\GeoUtils;
use App\Http\Controllers\PathFinderApi\FormattedPath;
use App\Http\Controllers\OtpPathFinder\OtpPathFormatter;
use App\Http\Controllers\PathFinderApi\PathCombiner;
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
use Carbon\Carbon;
use HeuristicEstimatorDijkstra;
use Illuminate\Http\Request;
use PathNode;
use Thread;


include "PathFinderApi/DataRetrieving/DataRetriever.php";
include "PathFinderApi/GraphGeneratorClasses/PathFinder.php";

class PathFinderController extends Controller
{

    private static $PATHFINDERURL="http://localhost:8080/path";
    //private static $PATHFINDERURL="https://archidni-path-finder-1.herokuapp.com/path";
    private static $path_finder_data_generator_url="http://localhost:8080/generatePath";
    //private static $path_finder_data_generator_url = "https://archidni-path-finder-1.herokuapp.com/generatePath";
    private static $MAX_DURATION = "300";

    private static $OTP_URL = "http://localhost:8801/otp/routers/default/plan?";


    public function findPath(Request $request)
    {
        if (isset($_GET)) {
            //$attributes = \DataRetriever::retrieveAttributes($_GET);
        }
        /*$url = self::$PATHFINDERURL."?origin=".$attributes['origin'][0].",".$attributes['origin'][1]."&destination=".
            $attributes['destination'][0].",".$attributes['destination'][1]."&time=".$_GET['time']."&day=".$attributes['day'];
        $pathJson = file_get_contents($url);
        $root = json_decode($pathJson);
        $paths = $root->formattedPaths;
        $pathsFormatter = new PathsFormatter($paths,false);
        $combinedPaths = (new PathCombiner())->getCombinedPaths($pathsFormatter->formatPaths());
        $combinedPaths = $this->getOnlyShortDurationPaths($combinedPaths);
        return response()->json($combinedPaths);*/
        //return $this->findPathsUsingOtp($request->all());
        //return response()->json($this->findPathsUsingOtp($request->all()));
        return response()->json($this->findPathsUsingSpring($request->all()));
    }

    private function findPathsUsingSpring ($attributes)
    {
        $origin = $attributes['origin'];
        $destination = $attributes['destination'];
        $date = $attributes['date'];
        $time = $attributes['time'];
        $url = "http://localhost:8080/OTPpath?origin=$origin&destination=$destination&date=$date"."&time=".$time.
            "&arriveBy=".$attributes['arriveBy']."&directWalking=false";
        $otpPathFormatter = new OtpPathFormatter($attributes['origin'],$attributes['destination'],
            file_get_contents($url."&numItineraries=6"));
        $paths = $otpPathFormatter->getFormattedPaths();
        return $paths;
    }

    private function findPathsUsingOtp ($attributes)
    {
        $url =  self::$OTP_URL."fromPlace=".$attributes['origin']."&toPlace=".$attributes['destination']."&time=".
            $attributes['time']."&date=".$this->getDateString($attributes['date'])."&mode=TRANSIT,WALK&arriveBy=false";
        $otpPathFormatter = new OtpPathFormatter($attributes['origin'],$attributes['destination'],file_get_contents($url."&numItineraries=6"));
        $paths = $otpPathFormatter->getFormattedPaths();
        $url = $url."&bannedAgencies=3";
        $otpPathFormatter = new OtpPathFormatter($attributes['origin'],$attributes['destination'],file_get_contents($url."&numItineraries=3"));
        $paths = array_merge($paths,$otpPathFormatter->getFormattedPaths());


        return $paths;
    }

    private function getDateString ($date)
    {
        return date("d-m-y",$date);
    }

    public function formatPaths (Request $request)
    {
        $root = json_decode($request->getContent());
        $paths = $root->formattedPaths;
        $pathsFormatter = new PathsFormatter($paths,false);
        $combinedPaths = (new PathCombiner())->getCombinedPaths($pathsFormatter->formatPaths());
        $combinedPaths = $this->getOnlyShortDurationPaths($combinedPaths);
        return response()->json($combinedPaths);
    }

    private function getOnlyShortDurationPaths ($combinedPaths)
    {
        $paths = [];
        for ($i=0;$i<count($combinedPaths);$i++)
        {
            $path = $combinedPaths[$i];
            $formattedPath = new FormattedPath($path);
            if ($formattedPath->getDuration()<self::$MAX_DURATION)
            {
                array_push($paths,$path);
            }
        }
        return $paths;
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
        //return response()->json($data);
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
            $formattedMetroTrip = array('id'=>$trip->id,'lineId'=>$trip->line_id,'days'=>$trip->days);
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
