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
     * @param PathFinderAttributes $pathFinderAttributes
     */
    public function __construct($pathFinderAttributes)
    {
        $this->pathFinderAttributes = $pathFinderAttributes;
    }

    public function findPaths()
    {

        $directWalkingPaths = $this->findPathsDirectWalking();
        $streetWalkingPaths = $this->findPathsStreetWalking();
        $walkingCachEntries = [];
        $walkingCach = new WalkingCache($walkingCachEntries);
        foreach ($streetWalkingPaths as $path)
        {
            /**
             * @var $path OtpPathIntermediate
             */
            $entries = $path->getWalkingCacheEntries();
            foreach ($entries as $entry)
            {
                $walkingCach->addEntry($entry);
            }
        }
        $originsDestinations = [];
        foreach ($directWalkingPaths as $path)
        {
            /**
             * @var $path OtpPathIntermediate
             */
            $entries = $path->getWalkingCacheEntries();
            foreach ($entries as $entry)
            {
                /**
                 * @var $entry WalkingCacheEntry
                 */
                if (!$walkingCach->contains($entry))
                {
                    $originDestination = ['origin'=>$entry->getOrigin(),'destination'=>
                        $entry->getDestination()];
                    if (!in_array($originDestination,$originsDestinations))
                        array_push($originsDestinations,$originDestination);
                }
            }
        }
        $walkingPathFinder = new WalkingPathFinder($originsDestinations);
        $newCashEntries = $walkingPathFinder->getPaths();
        foreach ($newCashEntries as $newCashEntry)
        {
            $walkingCach->addEntry($newCashEntry);
        }
        $newDirectPaths = [];
        foreach ($directWalkingPaths as $directWalkingPath)
        {
            $pathWalkingAdjuster = new WalkingPathAdjuster($directWalkingPath,$walkingCach);
            array_push($newDirectPaths,$pathWalkingAdjuster->getAdjustedPath());
        }
        $newDirectPaths = array_merge($newDirectPaths,$streetWalkingPaths);
        $paths = [];
        foreach ($newDirectPaths as $path)
        {
            $formatter = new OtpIntermediateToOutputPathFormatter($path);
            array_push($paths,$formatter->formatPath());
        }
        return $paths;
    }


    private function createPathUrl ($directWalking)
    {
        $attributes = $this->pathFinderAttributes;
        $origin = $attributes->getOrigin();
        $destination = $attributes->getDestination();
        $date = $attributes->getDate();
        $time = $attributes->getTime();
        $originStr = $origin->getLatitude().",".$origin->getLongitude();
        $destinationStr = $destination->getLatitude().",".$destination->getLongitude();
        $directWalking = ($directWalking) ? "true" : "false";
        return "http://localhost:8080/OTPpath?origin=$originStr&destination=$destinationStr&date=$date"."&time=".$time.
            "&arriveBy=".$attributes->getArriveBy()."&directWalking=".$directWalking;
    }

    private function findPathsDirectWalking ()
    {
        $attributes = $this->pathFinderAttributes;
        $url = $this->createPathUrl(true);
        $json = file_get_contents($url."&numItineraries=".$this->numItineraries);
        $otpIntermediatePathFormatter = new OtpIntermediatePathFormatter($json,$attributes);
        $formattedPaths = $otpIntermediatePathFormatter->getFormattedPaths();
        return $formattedPaths;
    }

    private function findPathsStreetWalking ()
    {
        $attributes = $this->pathFinderAttributes;
        $url = $this->createPathUrl(false);
        $otpIntermediatePathFormatter = new OtpIntermediatePathFormatter(file_get_contents($url."&numItineraries=".$this->numItineraries)
            ,$attributes);
        return $otpIntermediatePathFormatter->getFormattedPaths();
    }


}