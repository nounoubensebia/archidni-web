<?php

/**
 * Created by PhpStorm.
 * User: ressay
 * Date: 15/02/18
 * Time: 11:32
 */
class GraphStation
{
    private $id;
    private $name;
    private $latitude;
    private $longitude;
    private $trip;
    private $minute;

    /**
     * GraphStation constructor.
     * @param $id
     * @param $name
     * @param $latitude
     * @param $longitude
     * @param $minute
     * @param $trip GraphTrip
     */
    private function __construct($id, $name, $latitude, $longitude, $minute, $trip)
    {
        $this->id = $id;
        $this->name = $name;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->minute = $minute;
        $this->trip = $trip;
    }

    /**
     * @param $station \App\Station
     * @param $minutes
     * @param $trip GraphTrip
     * @return GraphStation
     */

    public static function loadFromStation($station,$minutes,$trip)
    {
        return new GraphStation($station->id,$station->name,$station->latitude,
                                $station->longitude,$minutes,$trip);
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getMinute()
    {
        return $this->minute;
    }

    /**
     * @param mixed $minute
     */
    public function setMinute($minute)
    {
        $this->minute = $minute;
    }




    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param mixed $latitude
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * @return mixed
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param mixed $longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * @return GraphTrip
     */
    public function getTrip()
    {
        return $this->trip;
    }



    // methods to override

    public function getTag()
    {
        return $this->getTrip()->getTag().":".$this->getId().':'.$this->getName();
    }

    private static function getDefaultTime()
    {
        return UtilFunctions::getCurrentTime();
    }

    public function getNextDeparture($time = null)
    {
        if($time == null) $time = self::getDefaultTime();
        //TODO
    }

    public function getWaitingTime($time = null)
    {
        if($time == null) $time = self::getDefaultTime();
        return $this->getTrip()->getWaitingTimeOfStation($this,$time);
    }

    public function getWaitingTimeAtTrip($time = null)
    {
        if($time == null) $time = self::getDefaultTime();
        return 0;
    }

    public function hasExactWaitingTime()
    {
        return $this->getTrip()->hasExactWaitingTime();
    }
}