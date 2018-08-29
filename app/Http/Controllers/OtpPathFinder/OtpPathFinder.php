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
    private $numItineraries = 4;
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
        $debug = [];
        $beforeAll = round(microtime(true) * 1000);
        //getting paths
        $before = round(microtime(true) * 1000);
        $directWalkingPaths = $this->findPathsDirectWalking();
        $streetWalkingPaths = $this->findPathsStreetWalking();
        $after = round(microtime(true) * 1000);
        $debug['getting_and_formatting_intermediate_paths'] = $after-$before;
        //initializing walking cache in order to do not calculate the same walking more than a single time
        $walkingCacheEntries = [];
        $walkingCache = new WalkingCache($walkingCacheEntries);
        $before = round(microtime(true) * 1000);
        foreach ($streetWalkingPaths as $path)
        {
            /**
             * @var $path OtpPathIntermediate
             */
            $entries = $path->getWalkingCacheEntries();
            foreach ($entries as $entry)
            {
                $walkingCache->addEntry($entry);
            }
        }

        //getting walking paths to be calculated

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
                if (!$walkingCache->contains($entry))
                {
                    $originDestination = ['origin'=>$entry->getOrigin(),'destination'=>
                        $entry->getDestination()];
                    if (!in_array($originDestination,$originsDestinations))
                        array_push($originsDestinations,$originDestination);
                }
            }
        }

        //getting walking paths
        $walkingPathFinder = new WalkingPathFinder($originsDestinations);
        $newCashEntries = $walkingPathFinder->getPaths();
        foreach ($newCashEntries as $newCashEntry)
        {
            $walkingCache->addEntry($newCashEntry);
        }

        $after = round(microtime(true) * 1000);
        $debug['getting_walk_paths'] = $after-$before;

        //adjusting walking portions of paths
        $before = round(microtime(true) * 1000);
        $allPaths = array_merge($directWalkingPaths,$streetWalkingPaths);
        $allPaths = array_unique($allPaths,SORT_REGULAR);
        $allPaths = array_values($allPaths);
        $adjustedPaths = [];
        foreach ($allPaths as $path)
        {
            $pathWalkingAdjuster = new WalkingPathAdjuster($path,$walkingCache);
            array_push($adjustedPaths,$pathWalkingAdjuster->getAdjustedPath());
        }
        $adjustedPaths = array_unique($adjustedPaths,SORT_REGULAR);
        $adjustedPaths = array_values($adjustedPaths);
        $after = round(microtime(true) * 1000);
        $debug['adjusting_walk_paths'] = $after-$before;
        //adding other possible trips
        /*$before = round(microtime(true) * 1000);
        foreach ($adjustedPaths as $adjustedPath)
        {
            $commonSectionsFinder = new OtpPathCommonSectionsFinder($adjustedPath);
            $commonSectionsFinder->addPossibleTrips();
        }
        $after = round(microtime(true) * 1000);
        $debug['getting_common_sections'] = $after-$before;*/

        $before = round(microtime(true) * 1000);
        //formatting paths for output
        $formattedPaths = [];
        foreach ($adjustedPaths as $path)
        {
            $formatter = new OtpIntermediateToOutputPathFormatter($path);
            array_push($formattedPaths,$formatter->formatPath());
        }
        $formattedPaths = array_unique($formattedPaths,SORT_REGULAR);
        $formattedPaths = array_values($formattedPaths);
        $after = round(microtime(true) * 1000);
        $debug['formatting_paths_for_output'] = $after-$before;
        $afterAll = round(microtime(true) * 1000);
        $debug['total'] = $afterAll-$beforeAll;
        return new OtpPathFinderResponse($formattedPaths,$debug);
    }


    private function createPathUrl ($directWalking,$withoutBus)
    {
        $attributes = $this->pathFinderAttributes;
        $origin = $attributes->getOrigin();
        $destination = $attributes->getDestination();
        $date = $attributes->getDate();
        $time = $attributes->getTime();
        $originStr = $origin->getLatitude().",".$origin->getLongitude();
        $destinationStr = $destination->getLatitude().",".$destination->getLongitude();
        $directWalking = ($directWalking) ? "true" : "false";
        $withoutBus = ($withoutBus) ? "true" : "false";
        return "http://localhost:8080/OTPpath?origin=$originStr&destination=$destinationStr&date=$date"."&time=".$time.
            "&arriveBy=".$attributes->getArriveBy()."&directWalking=".$directWalking."&withoutBus=".$withoutBus;
    }

    private function findPathsDirectWalking ()
    {
        $attributes = $this->pathFinderAttributes;
        $url = $this->createPathUrl(true,false);
        $paths = $this->getPathsFromOtpServer($url,$attributes,$this->numItineraries);
        $url = $this->createPathUrl(true,true);
        $paths = array_merge($paths,$this->getPathsFromOtpServer($url,$attributes,$this->numItineraries));
        $paths = array_unique($paths,SORT_REGULAR);
        $paths = array_values($paths);
        return $paths;
    }

    private function findPathsStreetWalking ()
    {
        $attributes = $this->pathFinderAttributes;
        $url = $this->createPathUrl(false,false);
        $paths = $this->getPathsFromOtpServer($url,$attributes,$this->numItineraries);
        $url = $this->createPathUrl(false,true);
        $paths = array_merge($paths,$this->getPathsFromOtpServer($url,$attributes,$this->numItineraries));
        $paths = array_unique($paths,SORT_REGULAR);
        $paths = array_values($paths);
        return $paths;
    }

    private function getPathsFromOtpServer ($url,$attributes,$numItineraries)
    {
        $json = file_get_contents($url."&numItineraries=".$numItineraries);
        $otpIntermediatePathFormatter = new OtpIntermediatePathFormatter($json,$attributes);
        $formattedPaths = $otpIntermediatePathFormatter->getFormattedPaths();
        return $formattedPaths;
    }
}