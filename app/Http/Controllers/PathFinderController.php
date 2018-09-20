<?php

namespace App\Http\Controllers;

use App\GeoUtils;
use App\Http\Controllers\OtpPathFinder\Coordinate;
use App\Http\Controllers\OtpPathFinder\OtpPathFinder;
use App\Http\Controllers\OtpPathFinder\PathFinderAttributes;
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
use DateTime;
use HeuristicEstimatorDijkstra;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PathNode;
use Thread;


include "PathFinderApi/DataRetrieving/DataRetriever.php";
include "PathFinderApi/GraphGeneratorClasses/PathFinder.php";

class PathFinderController extends Controller
{

    public function findPath(Request $request)
    {
        return response()->json($this->findPathsUsingSpring($request->all()),200);
    }

    private function findPathsUsingSpring ($attributes)
    {
        DB::enableQueryLog();
        $originStr = explode(",",$attributes['origin']);
        $destinationStr = explode(",",$attributes['destination']);
        $origin = new Coordinate($originStr[0],$originStr[1]);
        $destination = new Coordinate($destinationStr[0],$destinationStr[1]);
        $date = $attributes['date'];
        $time = $attributes['time'];
        $arriveBy = (strcmp($attributes['arriveBy'],"true")==0) ? true : false;
        $otpPathFinder = new OtpPathFinder(new PathFinderAttributes($origin,$destination,$time,$date,$arriveBy));
        $paths = $otpPathFinder->findPaths();
        return $paths;
    }
}
