<?php

namespace App\Http\Controllers;

use App\Http\Controllers\PathFinderApi\PathRetriever;
use App\Line;
use App\MetroTrip;
use App\Station;
use AStar;
use HeuristicEstimatorDijkstra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PathNode;
use PathTransformer;


include "PathFinderApi/DataRetrieving/DataRetriever.php";
include "PathFinderApi/GraphGeneratorClasses/PathFinder.php";
include "PathFinderApi/PathTransformer.php";

class PathFinderController extends Controller
{

    public function findPath()
    {
        $tot = 0;
        DB::connection()->enableQueryLog();
        DB::listen(function ($query) use (&$tot) {
            // $query->sql
            // $query->bindings
            $tot+=$query->time;
        });
        $attributes = [];
        if (isset($_GET)) {
            $attributes = \DataRetriever::retrieveAttributes($_GET);
        }
        $attributes['MaxWalkingTimePerCorrespondence'] = 25;
        //$attributes['transportMeanUnused'] = [3];
        $result = PathRetriever::getAllPaths($attributes,3);
        $pathsTransformed = array();
        foreach ($result as $path) {
            $transformedPath = new PathTransformer($path);
            $trPath = $transformedPath->getTransformedPath();
            //print_r($trPath);
            //TODO re implement this
            /*foreach ($trPath as &$instruction)
            {
                if (strcmp($instruction['type'],"walk_instruction")==0)
                {
                    $birdPolyline = $instruction['polyline'];
                    //print_r($birdPolyline);
                    $realPolyline = $this->getWalkingPolyline($birdPolyline[0],$birdPolyline[1]);
                    //print_r($realPolyline);
                    $instruction['polyline'] = $realPolyline;
                    //return json_encode($instruction['polyline']);
                }
            }*/
            array_push($pathsTransformed,$trPath );
        }

        /*$total_time = 0;
        $queries = DB::getQueryLog();
        $sqls = "";
        /*foreach ($queries as $query)
        {
            $q = $query['query'];
            $total_time+=$query['time'];
            $bindings = $query['bindings'];
            foreach ($bindings as $binding)
            {
                $pos = strpos($q,'?');
                $q = substr_replace($q,$binding,$pos,1);
            }

            $sqls.=";\n".$q;
        }*/
        //return $sqls;
        //return response()->json($queries);
        //return $tot;
        //return count($queries);
        //return response()->json($queries);
        return response()->json($pathsTransformed);
        //return response()->json($result);
    }

    function getWalkingPolyline ($origin,$destination)
    {
        $url = "https://maps.googleapis.com/maps/api/directions/json?&mode=walking&origin=".$origin['latitude'].",".$origin['longitude'].
            "&destination=".$destination['latitude'].",".$destination['longitude']."&key=AIzaSyCq7gRmzi9W5QczpfHJZNXZR3tpZYmZPSw";
        $response = file_get_contents($url);
        $obj = json_decode($response);
        $routes = $obj->{'routes'};
        $route = $routes[0];
        $overviewPolyline = $route->{'overview_polyline'};
        $points = $overviewPolyline->{'points'};
        return $this->decodePolyline($points);
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
