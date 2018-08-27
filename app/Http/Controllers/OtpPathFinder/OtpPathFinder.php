<?php
/**
 * Created by PhpStorm.
 * User: noureddine bensebia
 * Date: 23/08/2018
 * Time: 21:25
 */

namespace App\Http\Controllers\OtpPathFinder;


class OtpPathFinder
{
    private $pathFinderAttributes;
    private static $URL = "http://localhost:8801/otp/routers/default/plan?";
    private $numItineraries = 6;
    /**
     * OtpPathFinder constructor.
     * @param $pathFinderAttributes
     */
    public function __construct($pathFinderAttributes)
    {
        $this->pathFinderAttributes = $pathFinderAttributes;
    }

    public function findPaths()
    {
        $directWalkingPaths = $this->findPathsDirectWalking();
        return $directWalkingPaths;
    }

    private function createPathUrl ($directWalking)
    {
        $attributes = $this->pathFinderAttributes;
        $origin = $attributes->getOrigin();
        $destination = $attributes->getDestination();
        $date = $attributes->getDate();
        $time = $attributes->getTime();
        return "http://localhost:8080/OTPpath?origin=$origin&destination=$destination&date=$date"."&time=".$time.
            "&arriveBy=".$attributes->getArriveBy()."&directWalking=".$directWalking;
    }

    private function findPathsDirectWalking ()
    {
        $attributes = $this->pathFinderAttributes;
        $url = $this->createPathUrl(true);
        /*$otpPathFormatter = new OtpPathFormatter( $attributes->getOrigin(), $attributes->getDestination(),
            file_get_contents($url."&numItineraries=6"));*/
        $otpIntermediatePathFormatter = new OtpIntermediatePathFormatter(file_get_contents($url."&numItineraries=".$this->numItineraries)
            ,$attributes);
        return $otpIntermediatePathFormatter->getFormattedPaths();
    }

    private function findPathsStreetWalking ()
    {
        $attributes = $this->pathFinderAttributes;
        $url = $this->createPathUrl(false);
        $otpPathFormatter = new OtpPathFormatter($attributes['origin'],$attributes['destination'],
            file_get_contents($url."&numItineraries=6"));
        return $otpPathFormatter->getFormattedPaths();
    }


}