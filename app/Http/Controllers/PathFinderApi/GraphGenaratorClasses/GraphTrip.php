<?php

/**
 * Created by PhpStorm.
 * User: ressay
 * Date: 15/02/18
 * Time: 12:43
 */

include "GraphStation.php";
class GraphTrip
{
    private $id;
    private $stations;
    private $departures;
    private $timePeriods;
    private $line;

    // methods
    private $getEdgeVal;
    private $getWaitingTime;

    /**
     * GraphTrip constructor.
     * @param $id
     * @param $line \App\Line
     */
    private function __construct($id,$line)
    {
        $this->id = $id;
        $this->line = $line;
    }

    private static function loadTrip($trip)
    {
        $graphTrip = new GraphTrip($trip->id,$trip->line);
        $graphStations = [];
        foreach ($trip->stations as $station) {
            $graphStations[] = GraphStation::loadFromStation($station,$station->pivot->minutes,$graphTrip);
        }
        $graphTrip->setStations($graphStations);
        return $graphTrip;
    }

    public static function loadFromTrainTrip($trip)
    {
        $graphTrip = self::loadTrip($trip);
        $graphTrip->setDepartures($trip->departures);

        // set Methods

        $graphTrip->setGetEdgeVal("getEdgeValueTrain");
        $graphTrip->setGetWaitingTime("getWaitingTimeOfStationTrain");
        return $graphTrip;
    }


    public static function loadFromMetroTrip($trip)
    {
        $graphTrip = self::loadTrip($trip);
        $graphTrip->setTimePeriods($trip->timePeriods);

        // setMethods

        $graphTrip->setGetEdgeVal("getEdgeValueMetro");
        $graphTrip->setGetWaitingTime("getWaitingTimeOfStationMetro");
        return $graphTrip;
    }

    /**
     * @param mixed $getWaitingTime
     */
    public function setGetWaitingTime($getWaitingTime)
    {
        $this->getWaitingTime = $getWaitingTime;
    }




    /**
     * @param mixed $getEdgeVal
     */
    public function setGetEdgeVal($getEdgeVal)
    {
        $this->getEdgeVal = $getEdgeVal;
    }

    /**
     * @return \App\Line
     */
    public function getLine(): \App\Line
    {
        return $this->line;
    }

    /**
     * @param \App\Line $line
     */
    public function setLine(\App\Line $line)
    {
        $this->line = $line;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return array GraphStation
     */
    public function getStations()
    {
        return $this->stations;
    }

    /**
     * @param array GraphStation $stations
     */
    public function setStations($stations)
    {
        $this->stations = $stations;
    }

    /**
     * @return mixed
     */
    public function getDepartures()
    {
        return $this->departures;
    }

    /**
     * @param mixed $departures
     */
    public function setDepartures($departures)
    {
        $this->departures = $departures;
    }

    /**
     * @return mixed
     */
    public function getTimePeriods()
    {
        return $this->timePeriods;
    }

    /**
     * @param mixed $timePeriods
     */
    public function setTimePeriods($timePeriods)
    {
        $this->timePeriods = $timePeriods;
    }


    // trip methods

    public function getTag()
    {
        return $this->getId();
    }

    /**
     * @param $station1 GraphStation
     * @param $station2 GraphStation
     * @return mixed
     */

    public function getEdgeValue($station1,$station2)
    {
        $func = $this->getEdgeVal;
        return $this->$func($station1,$station2);
    }

    /**
     * @param $station1 GraphStation
     * @param $station2 GraphStation
     * @return mixed
     */

    private function getEdgeValueTrain($station1,$station2)
    {
        return $station2->getMinute()-$station1->getMinute();
    }

    /**
     * @param $station1 GraphStation
     * @param $station2 GraphStation
     * @return mixed
     */

    private function getEdgeValueMetro($station1,$station2)
    {
        return $station2->getMinute()-$station1->getMinute();
    }

    /**
     * @param $station GraphStation
     * @param $time
     * @return
     */
    public function getWaitingTimeOfStation($station,$time)
    {
        $func = $this->getWaitingTime;
        return $this->$func($station,$time);
    }

    /**
     * @param $station GraphStation
     * @param $time
     */
    private function getWaitingTimeOfStationTrain($station,$time)
    {

    }

    /**
     * @param $station GraphStation
     * @param $time
     * @return mixed
     */
    private function getWaitingTimeOfStationMetro($station,$time)
    {
        $t = UtilFunctions::strToMin($time);
        $minStartT = 24*60;
        $minWaitingTime = 24*60;
        foreach ($this->getTimePeriods() as $timePeriod) {
            $strtT = UtilFunctions::strToMin($timePeriod->start);
            $endT = UtilFunctions::strToMin($timePeriod->end);
            if($t <= $endT && $t >= $strtT)
            {
                return $timePeriod->waiting_time;
            }
            else
            {
                if($strtT > $t)
                {
                    $minWaitingTime = ($minStartT<$strtT-$t)?$minWaitingTime:$timePeriod->waiting_time;
                    $minStartT = self::minA($minStartT,$strtT-$t);
                }
                else
                {
                    $minWaitingTime = ($minStartT<$strtT+24*60-$t)?$minWaitingTime:$timePeriod->waiting_time;
                    $minStartT = self::minA($minStartT,$strtT+24*60-$t);
                }
            }
        }
        return $minStartT+$minWaitingTime;
    }

    private static function minA($a,$b){return ($a<$b)?$a:$b;}

}