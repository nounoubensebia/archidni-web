<?php

/**
 * Created by PhpStorm.
 * User: ressay
 * Date: 10/02/18
 * Time: 15:35
 */




class StationGenerator
{
    static private $distance = 1000000;

    static public function getStationsByFoot($position)
    {
        $stations = \App\Station::all();
        $result = [];
        foreach ($stations as $station)
        {
            if(self::stationAvailableByFoot($station,$position))
                $result[] = $station;
        }
        return $result;
    }

    static private function stationAvailableByFoot($station,$position)
    {
        $p1 = [$station->latitude,$station->longitude];
        $p2 = $position;
        return UtilFunctions::getDistance($p1,$p2) < self::getMaxDistanceToStationByFoot();
    }

    static private function getMaxDistanceToStationByFoot()
    {
        return self::$distance;
    }
}