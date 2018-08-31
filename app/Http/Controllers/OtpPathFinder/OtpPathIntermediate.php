<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 27/08/2018
 * Time: 18:47
 */

namespace App\Http\Controllers\OtpPathFinder;


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
}