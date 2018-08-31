<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 28/08/2018
 * Time: 18:18
 */

namespace App\Http\Controllers\OtpPathFinder\PathInstruction;


use App\Http\Controllers\OtpPathFinder\Coordinate;

class RideInstructionIntermediate extends RideInstruction implements \JsonSerializable
{

    private $waitLinesIntermediate;

    /**
     * RideInstructionIntermediate constructor.
     * @param $stations
     * @param $ride_duration
     * @param Coordinate $coordinate
     * @param $lines
     */
    public function __construct($polyline,$stations,$ride_duration,Coordinate $coordinate,$errorMargin, $lines)
    {
        parent::__construct($polyline,$coordinate,$stations,$ride_duration,$errorMargin);
        $this->waitLinesIntermediate = $lines;
    }


    /**
     * @return mixed
     */
    public function getWaitLinesIntermediate()
    {
        return $this->waitLinesIntermediate;
    }

    public function getStationStartId ()
    {
        return $this->getStations()[0]['id'];
    }

    public function getStationEndId ()
    {
        return $this->getStations()[count($this->getStations())-1]['id'];
    }

    public function addWaitLine (WaitLineIntermediate $waitLineIntermediate)
    {
        if (!$this->containsWaitLine($waitLineIntermediate))
        array_push($this->waitLinesIntermediate,$waitLineIntermediate);
    }

    private function containsWaitLine (WaitLineIntermediate $waitLineIntermediate)
    {
        foreach ($this->waitLinesIntermediate as $item)
        {
            if ($item->getTrip()->id==$waitLineIntermediate->getTrip()->id)
                return true;
        }
        return false;
    }

    public function jsonSerialize()
    {
        $var = get_object_vars($this);
        $var['type'] = "wait_instruction";
        foreach ($var as &$value) {
            if (is_object($value) && method_exists($value,'getJsonData')) {
                $value = $value->getJsonData();
            }
        }
        return $var;
    }


}