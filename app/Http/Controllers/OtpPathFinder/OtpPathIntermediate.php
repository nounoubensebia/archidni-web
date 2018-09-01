<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 27/08/2018
 * Time: 18:47
 */

namespace App\Http\Controllers\OtpPathFinder;


use App\Http\Controllers\OtpPathFinder\PathInstruction\RideInstructionIntermediate;
use App\Http\Controllers\OtpPathFinder\PathInstruction\WaitLineIntermediate;
use App\Http\Controllers\OtpPathFinder\PathInstruction\WalkInstruction;

class OtpPathIntermediate implements \JsonSerializable
{
    private $directWalking;
    private $pathFinderAttributes;
    private $instructions;

    /**
     * OtpPath constructor.
     * @param $directWalking
     * @param $pathFinderAttributes
     * @param $instructions
     */
    public function __construct($directWalking, $pathFinderAttributes, $instructions)
    {
        $this->directWalking = $directWalking;
        $this->pathFinderAttributes = $pathFinderAttributes;
        $this->instructions = $instructions;
    }

    /**
     * @return mixed
     */
    public function getDirectWalking()
    {
        return $this->directWalking;
    }

    /**
     * @return mixed
     */
    public function getPathFinderAttributes()
    {
        return $this->pathFinderAttributes;
    }

    /**
     * @return mixed
     */
    public function getInstructions()
    {
        return $this->instructions;
    }

    public function getWalkingCacheEntries ()
    {
        $entries = [];
        foreach ($this->instructions as $instruction)
        {
            if ($instruction instanceof WalkInstruction)
            {
                $origin = $instruction->getOrigin();
                $destination = $instruction->getDestination();
                $polyline = $instruction->getPolyline();
                array_push($entries,new WalkingCacheEntry($origin,$destination,$polyline));
            }
        }
        return $entries;
    }



    public function getPathDuration ()
    {
        $duration = 0;
        foreach ($this->instructions as $instruction)
        {
            if ($instruction instanceof WalkInstruction)
            {
                $duration+= $instruction->getDuration();
            }
            else
            {
                /**
                 * @var $instruction RideInstructionIntermediate
                 */
                $duration+=$instruction->getRideDuration();
                $duration+=$this->getWaitLinesDuration($instruction->getWaitLinesIntermediate());
            }
        }
        return $duration;
    }

    private function getWaitLinesDuration ($waitLinesArray)
    {
        $min = 10000000;
        foreach ($waitLinesArray as $item)
        {
            /**
             * @var $item WaitLineIntermediate
             */
            if ($item->getDuration()<$min)
            {
                $min = $item->getDuration();
            }
        }
        return $min;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
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

    public function getPathLines ()
    {
        $lines = [];
        foreach ($this->instructions as $instruction)
        {
            if ($instruction instanceof RideInstructionIntermediate)
            {
                $ll = [];
                foreach ($instruction->getWaitLinesIntermediate() as $item)
                {
                    /**
                     * @var $item WaitLineIntermediate
                     */
                    array_push($ll,$item->getLine()->id);
                }
                array_push($lines,$ll);
            }
        }
        return $lines;
    }

}