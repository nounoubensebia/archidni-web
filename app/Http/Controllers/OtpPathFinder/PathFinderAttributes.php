<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 27/08/2018
 * Time: 18:37
 */

namespace App\Http\Controllers\OtpPathFinder;




class PathFinderAttributes
{
    private $origin;
    private $destination;
    private $time;
    private $date;
    private $arriveBy;

    /**
     * PathFinderAttributes constructor.
     * @param Coordinate $origin
     * @param Coordinate $destination
     * @param $time
     * @param $date
     * @param $arriveBy
     */
    public function __construct($origin, $destination, $time, $date, $arriveBy)
    {
        $this->origin = $origin;
        $this->destination = $destination;
        $this->time = $time;
        $this->date = $date;
        $this->arriveBy = $arriveBy;
    }


    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return mixed
     */
    public function getArriveBy()
    {
        return $this->arriveBy;
    }

    /**
     * @return Coordinate
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @return Coordinate
     */
    public function getDestination()
    {
        return $this->destination;
    }




}