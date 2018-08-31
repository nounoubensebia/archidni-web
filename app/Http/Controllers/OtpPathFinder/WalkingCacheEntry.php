<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 28/08/2018
 * Time: 15:42
 */

namespace App\Http\Controllers\OtpPathFinder;


class WalkingCacheEntry
{
    /**
     * @var Coordinate
     */
    private $origin;
    /**
     * @var Coordinate
     */
    private $destination;
    /**
     * @var array
     */
    private $polyline;

    /**
     * WalkingCache constructor.
     * @param $origin
     * @param $destination
     * @param $polyline
     */
    public function __construct($origin, $destination, $polyline)
    {
        $this->origin = $origin;
        $this->destination = $destination;
        $this->polyline = $polyline;
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




}