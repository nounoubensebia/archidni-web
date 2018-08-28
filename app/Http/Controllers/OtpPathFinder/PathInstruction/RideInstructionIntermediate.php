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
    /**
     * @var Coordinate
     */
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