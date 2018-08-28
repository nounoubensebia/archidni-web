<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 28/08/2018
 * Time: 18:19
 */

namespace App\Http\Controllers\OtpPathFinder\PathInstruction;


class WaitLineIntermediate extends WaitLine implements \JsonSerializable
{
    private $line;
    private $trip;

    /**
     * WaitLineIntermediate constructor.
     * @param $line
     * @param $trip
     * @param $transportModeId
     * @param $duration
     * @param $destination
     * @param $exactWaitingTime
     * @param $hasPerturbations
     */
    public function __construct($line, $trip,$transportModeId, $duration, $destination, $exactWaitingTime, $hasPerturbations)
    {
        parent::__construct($transportModeId,$duration,$destination,$exactWaitingTime,$hasPerturbations);
        $this->line = $line;
        $this->trip = $trip;
    }

    /**
     * @return mixed
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @return mixed
     */
    public function getTrip()
    {
        return $this->trip;
    }

    /**
     * WaitLineIntermediate constructor.
     * @param $line
     * @param $trip
     */


    /**
     * WaitLineIntermediate constructor.
     * @param $line
     * @param $trip
     * @param $transportModeId
     * @param $duration
     * @param $destination
     * @param $exactWaitingTime
     * @param $hasPerturbations
     */


    /**
     * WaitLineIntermediate constructor.
     * @param $id
     * @param $lineName
     * @param $transportModeId
     * @param $duration
     * @param $destination
     * @param $exactWaitingTime
     * @param $hasPerturbations
     */





    public function jsonSerialize()
    {
        $var = get_object_vars($this);
        foreach ($var as &$value) {
            if (is_object($value) && method_exists($value,'getJsonData')) {
                $value = $value->getJsonData();
            }
        }
        return $var;
    }


}