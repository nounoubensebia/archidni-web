<?php
/**
 * Created by PhpStorm.
 * User: noureddine bensebia
 * Date: 23/08/2018
 * Time: 21:25
 */

namespace App\Http\Controllers\OtpPathFinder;


use App\Http\Controllers\OtpPathFinder\DataLoader\PathsDataLoader;
use Illuminate\Support\Facades\DB;

class OtpPathFinder
{

    private $pathFinderAttributes;
    private static $URL = "http://localhost:8801/otp/routers/default/plan?";
    private $numItineraries = 6;
    private $transferPenalty = 0;
    /**
     * @var Context
     */
    private $context;
    /**
     * OtpPathFinder constructor.
     * @param PathFinderAttributes $pathFinderAttributes
     */
    public function __construct($pathFinderAttributes)
    {
        $this->pathFinderAttributes = $pathFinderAttributes;
        $this->context = new Context($this->pathFinderAttributes);
    }

    public function findPaths()
    {
        DB::enableQueryLog();
        $beforeAll = round(microtime(true) * 1000);
        //getting itineraries
        $before = round(microtime(true) * 1000);
        $directWalkingItineraries = $this->retreiveDirectWalkingItineraries();
        $streetWalkingItineraries = $this->retreiveStreetWalkingItineraries();
        $after = round(microtime(true) * 1000);

        //adding elapsed time to debug
        $this->context->addToDebug('getting_itineraries',($after-$before));

        // fetching data from data
        $this->prepareFormattingData(array_merge($directWalkingItineraries,$streetWalkingItineraries));

        //building intermediate paths

        $intermediatePathFormatter = new OtpIntermediatePathFormatter($this->context,$this->pathFinderAttributes);


        $before = Utils::getTimeInMilis();
        $streetWalkingPaths = $intermediatePathFormatter->getFormattedPaths($streetWalkingItineraries);
        $directWalkingPaths = $intermediatePathFormatter->getFormattedPaths($directWalkingItineraries);
        $after = Utils::getTimeInMilis();
        $this->context->incrementValue("intermediate_formatting",($after-$before));

        //initializing walking cache in order to do not calculate the same walking portion more than a single time

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
        //adding elapsed time to debug

        $this->context->addToDebug("getting_walk_paths",($after-$before));

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

        $this->context->addToDebug("adjusting_walking_paths",($after-$before));

        //adding other possible trips


        $before = round(microtime(true) * 1000);
        foreach ($adjustedPaths as $adjustedPath)
        {
            $commonSectionsFinder = new OtpPathCommonSectionsFinder($this->context);
            $commonSectionsFinder->addPossibleTrips($adjustedPath);
        }
        $after = round(microtime(true) * 1000);
        $this->context->addToDebug("adding common trips",($after-$before));
        $before = round(microtime(true) * 1000);

        // updating waiting times

        $otpPathUpdater = new OtpWaitingTimePathUpdater($this->context);

        foreach ($adjustedPaths as $adjustedPath)
        {
            $otpPathUpdater->updateWaitTimes($adjustedPath);
        }


        //filtering paths

        $otpPathFilter = new OtpPathFilter($this->context);
        $adjustedPaths = $otpPathFilter->getFilteredPaths($adjustedPaths);

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
        $this->context->addToDebug("formatting_paths_for_output",($after-$before));
        $afterAll = round(microtime(true) * 1000);
        $this->context->addToDebug("total",($afterAll-$beforeAll));
        $queryLog = DB::getQueryLog();
        $this->context->addToDebug("query_log",$queryLog);
        return new OtpPathFinderResponse($formattedPaths,$this->context->getDebug());
    }

    private function retreiveDirectWalkingItineraries ()
    {
        $otpServerClient = new OtpServerClient($this->pathFinderAttributes);
        $itineraries = $otpServerClient->getItineraries(true,false,$this->numItineraries,$this->transferPenalty);
        $itineraries = array_merge($itineraries,$otpServerClient->getItineraries(true,true,3,$this->transferPenalty));
        return $itineraries;
    }

    private function retreiveStreetWalkingItineraries()
    {
        $otpServerClient = new OtpServerClient($this->pathFinderAttributes);
        $itineraries = $otpServerClient->getItineraries(false,false,$this->numItineraries,$this->transferPenalty);
        $itineraries = array_merge($itineraries,$otpServerClient->getItineraries(false,true,3,$this->transferPenalty));
        return $itineraries;
    }

    private function prepareFormattingData ($itineraries)
    {
        $this->context->loadData($itineraries);
    }

}