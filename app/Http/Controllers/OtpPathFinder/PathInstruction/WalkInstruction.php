<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 28/08/2018
 * Time: 17:49
 */

namespace App\Http\Controllers\OtpPathFinder\PathInstruction;


class WalkInstruction extends Instruction
{
    private $origin;
    private $destination;
    private $polyline;
    private $duration;
    private $destinationName;

    /**
     * WalkInstruction constructor.
     * @param $origin
     * @param $destination
     * @param $polyline
     * @param $duration
     */
    public function __construct($origin, $destination, $polyline, $duration,$destinationName)
    {
        parent::__construct("walk_instruction");
        $this->origin = $origin;
        $this->destination = $destination;
        $this->polyline = $polyline;
        $this->duration = $duration;
        $this->destinationName = $destinationName;
    }

    /**
     * @return mixed
     */
    public function getOrigin()
    {
        return $this->origin;
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
    public function getPolyline()
    {
        return $this->polyline;
    }

    /**
     * @return mixed
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param mixed $polyline
     */
    public function setPolyline($polyline)
    {
        $this->polyline = $polyline;
    }

    /**
     * @param mixed $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    /**
     * @return mixed
     */
    public function getDestinationName()
    {
        return $this->destinationName;
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
        $var['type'] = "walk_instruction";
        foreach ($var as &$value) {
            if (is_object($value) && method_exists($value,'getJsonData')) {
                $value = $value->getJsonData();
            }
        }
        return $var;
    }
}