<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 20/08/2018
 * Time: 10:58
 */

namespace App\Http\Controllers\PathFinderApi;


use App\Station;

class PathServerHelper
{
    public function createStationRegionsMap ()
    {
        $stations = Station::with("trainTrips")->with("metroTrips")->get();
        return $stations;
    }
}