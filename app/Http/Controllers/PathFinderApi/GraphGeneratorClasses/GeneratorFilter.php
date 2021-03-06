<?php

/**
 * Created by PhpStorm.
 * User: ressay
 * Date: 21/02/18
 * Time: 18:01
 */
class GeneratorFilter
{
    private $origin;
    private $destination;
    private $day;
    private $time;
    private $unusedTransportLines;
    private $unusedTransportMeans;
    private $MaxWalkingTimePerCorrespondence;
    private $MaxWaitingTimePerCorrespondence;

    /**
     * GeneratorFilter constructor.
     * @param $origin
     * @param $destination
     * @param $day
     * @param $time
     */
    public function __construct($origin, $destination, $day, $time)
    {
        $this->origin = $origin;
        $this->destination = $destination;
        $this->day = $day;
        $this->time = $time;
    }

    /**
     * @return mixed
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @param mixed $origin
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;
    }

    /**
     * @return mixed
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @param mixed $destination
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
    }

    /**
     * @return mixed
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @param mixed $day
     */
    public function setDay($day)
    {
        $this->day = $day;
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param mixed $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     * @return mixed
     */
    public function getUnusedTransportLines()
    {
        return $this->unusedTransportLines;
    }

    /**
     * @param mixed $unusedTransportLines
     */
    public function setUnusedTransportLines($unusedTransportLines)
    {
        $this->unusedTransportLines = $unusedTransportLines;
    }

    /**
     * @return mixed
     */
    public function getUnusedTransportMeans()
    {
        return $this->unusedTransportMeans;
    }

    /**
     * @param mixed $unusedTransportMeans
     */
    public function setUnusedTransportMeans($unusedTransportMeans)
    {
        $this->unusedTransportMeans = $unusedTransportMeans;
    }

    /**
     * @param mixed $MaxWalkingTimePerCorrespondence
     */
    public function setMaxWalkingTimePerCorrespondence($MaxWalkingTimePerCorrespondence)
    {
        $this->MaxWalkingTimePerCorrespondence = $MaxWalkingTimePerCorrespondence;
    }

    /**
     * @param $walkingTime
     * @return bool
     */

    public function filterWalkingTimePerCorrespondence($walkingTime)
    {
        return $walkingTime < $this->MaxWalkingTimePerCorrespondence
            || $this->MaxWalkingTimePerCorrespondence == -1;
    }

    /**
     * @param $waitingTime
     * @return bool
     */
    public function filterWaitingTimePerCorrespondence($waitingTime)
    {
        return $waitingTime < $this->MaxWaitingTimePerCorrespondence
            || $this->MaxWaitingTimePerCorrespondence == -1;
    }

    /**
     * @param mixed $MaxWaitingTimePerCorrespondence
     */
    public function setMaxWaitingTimePerCorrespondence($MaxWaitingTimePerCorrespondence)
    {
        $this->MaxWaitingTimePerCorrespondence = $MaxWaitingTimePerCorrespondence;
    }




    /**
     * @param $station \App\Station
     * @return bool
     */
    public function filterStation($station)
    {
        return true;
    }

    public function filterTrip($trip)
    {

    }

}