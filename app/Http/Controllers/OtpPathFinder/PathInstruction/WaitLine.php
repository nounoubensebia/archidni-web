<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 28/08/2018
 * Time: 18:34
 */

namespace App\Http\Controllers\OtpPathFinder\PathInstruction;


abstract class WaitLine
{
    private $transportModeId;
    private $duration;
    private $destination;
    private $exactWaitingTime;
    private $hasPerturbations;

    /**
     * WaitLine constructor.
     * @param $transportModeId
     * @param $duration
     * @param $destination
     * @param $exactWaitingTime
     * @param $hasPerturbations
     */
    public function __construct($transportModeId, $duration, $destination, $exactWaitingTime, $hasPerturbations)
    {
        $this->transportModeId = $transportModeId;
        $this->duration = $duration;
        $this->destination = $destination;
        $this->exactWaitingTime = $exactWaitingTime;
        $this->hasPerturbations = $hasPerturbations;
    }

    /**
     * @return mixed
     */
    public function getTransportModeId()
    {
        return $this->transportModeId;
    }

    /**
     * @return mixed
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @return mixed
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @return mixed
     */
    public function getExactWaitingTime()
    {
        return $this->exactWaitingTime;
    }

    /**
     * @return mixed
     */
    public function getHasPerturbations()
    {
        return $this->hasPerturbations;
    }




}