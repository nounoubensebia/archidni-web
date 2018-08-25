<?php
/**
 * Created by PhpStorm.
 * User: noureddine bensebia
 * Date: 23/08/2018
 * Time: 19:46
 */

namespace App\Http\Controllers\PathFinderApi;


class CommonSectionsFinder
{
    private $rideInstruction;
    private $currentTime;
    private $day;

    /**
     * CommonSectionsFinder constructor.
     * @param $rideInstruction
     * @param $currentTime
     * @param $day
     */
    public function __construct($rideInstruction, $currentTime, $day)
    {
        $this->rideInstruction = $rideInstruction;
        $this->currentTime = $currentTime;
        $this->day = $day;
    }

    /**
     * CommonSectionsFinder constructor.
     * @param $rideInstruction
     */

    private function getAllPossibleTripsInstruction()
    {

    }


}