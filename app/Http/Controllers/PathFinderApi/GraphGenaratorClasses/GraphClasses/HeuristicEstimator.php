<?php

/**
 * Created by PhpStorm.
 * User: ressay
 * Date: 21/08/17
 * Time: 13:41
 */
abstract class HeuristicEstimator
{
    abstract public function run($from,$to);
}


/**
 * Class HeuristicEstimatorTry
 * will always estimate 0 and thus using this heuristic with A* is like using Dijkstra's algorithm
 */
class HeuristicEstimatorDijkstra extends HeuristicEstimator
{

    public function run($from, $to)
    {
        return 0;
    }
}

class HeuristicEstimatorDistance extends HeuristicEstimator
{
    function distance($lat1, $lon1, $lat2, $lon2) {

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;

        return ($miles * 1.609344);

    }

    function getDistance($pos1,$pos2)
    {
        return $this->distance($pos1[0],$pos1[1],$pos2[0],$pos2[1]);
    }

    public function run($from, $to)
    {
//        echo "distance between ".$from->getTag()." and ".$to->getTag()." is ".($this->getDistance($from->getHeuristicData(),$to->getHeuristicData())*12)."\n";
        return $this->getDistance($from->getHeuristicData(),$to->getHeuristicData())*12;
        // times 12 cause the distance is in km so times 1000 to get it in meters, and we expect a person to walk around 1.4m/s
        // so 84m/min and thus 1000/84 is approximately 12
    }
}