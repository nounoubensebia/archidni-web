<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 28/08/2018
 * Time: 18:32
 */

namespace App\Http\Controllers\OtpPathFinder\PathInstruction;


abstract class RideInstruction extends Instruction
{
    private $polyline;
    private $coordinate;
    private $stations;
    private $rideDuration;
    private $errorMargin;

    /**
     * RideInstruction constructor.
     * @param $coordinate
     * @param $stations
     * @param $rideDuration
     */
    public function __construct($polyline,$coordinate, $stations, $rideDuration,$errorMargin)
    {
        parent::__construct("ride_instruction");
        $this->polyline = $polyline;
        $this->coordinate = $coordinate;
        $this->stations = $stations;
        $this->rideDuration = $rideDuration;
        $this->errorMargin = $errorMargin;
    }
    /**
     * RideInstruction constructor.
     * @param $coordinate
     */


    /**
     * @return mixed
     */
    public function getCoordinate()
    {
        return $this->coordinate;
    }

    /**
     * @return mixed
     */
    public function getStations()
    {
        return $this->stations;
    }

    /**
     * @return mixed
     */
    public function getRideDuration()
    {
        return $this->rideDuration;
    }

    /**
     * @return mixed
     */
    public function getErrorMargin()
    {
        return $this->errorMargin;
    }

    /**
     * @return mixed
     */
    public function getPolyline()
    {
        return $this->polyline;
    }




}