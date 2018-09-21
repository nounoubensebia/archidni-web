<?php
/**
 * Created by PhpStorm.
 * User: noureddine bensebia
 * Date: 23/08/2018
 * Time: 19:46
 */

namespace App\Http\Controllers\PathFinderApi;


use App\CommonSection;
use App\Http\Controllers\LineHelper;
use App\Http\Controllers\OtpPathFinder\Context;
use App\Http\Controllers\OtpPathFinder\PathInstruction\RideInstructionIntermediate;
use App\Http\Controllers\OtpPathFinder\PathInstruction\WaitLineIntermediate;
use App\Http\Controllers\OtpPathFinder\Utils;
use DateTime;

class CommonSectionsFinder
{
    private $context;
    /**
     * @var RideInstructionIntermediate
     */
    private $rideInstruction;
    private $currentTime;
    private $day;

    /**
     * CommonSectionsFinder constructor.
     * @param RideInstructionIntermediate $rideInstruction
     * @param $currentTime
     * @param $day
     */
    public function __construct(Context $context,RideInstructionIntermediate $rideInstruction, $currentTime, $day)
    {
        $this->rideInstruction = $rideInstruction;
        $this->currentTime = $currentTime;
        $this->day = $day;
        $this->context = $context;
    }


    /**
     * CommonSectionsFinder constructor.
     * @param $rideInstruction
     */

    public function addPossibleTrips()
    {
        $waitLine = $this->rideInstruction->getWaitLinesIntermediate()[0];
        /**
         * @var $waitLine WaitLineIntermediate
         */
        $dateTime = DateTime::createFromFormat('!Y-m-d', $this->context->getPathFinderAttributes()->getDate());
        $day = date("w",$dateTime->format('U'));
        $startId = $this->rideInstruction->getStationStartId();
        $endId = $this->rideInstruction->getStationEndId();
        $trip = $waitLine->getTrip();
        $stations = $trip->stations;
        $commonSections = $trip->commonSections;
        foreach ($commonSections as $commonSection)
        {
            if ($this->tripContainsSection($commonSection,$startId,$endId,$stations))
            {
                foreach ($commonSection->metroTrips as $metroTrip)
                {
                    if (!(($metroTrip->id == $waitLine->getTrip()->id))&&Utils::isTripScheduledForDay($trip,$day))
                    {
                        $lineHelper = new LineHelper($metroTrip->line);
                        $this->rideInstruction->addWaitLine(new WaitLineIntermediate($metroTrip->line,
                            $metroTrip,$waitLine->getTransportModeId(),$waitLine->getDuration(),$waitLine->getDestination(),
                            false,$lineHelper->hasPerturbations()));
                    }
                }

                foreach ($commonSection->trainTrips as $trainTrip)
                {
                    if (!(($trainTrip->id == $waitLine->getTrip()->id)&&($waitLine->getExactWaitingTime())))
                    {
                        $lineHelper = new LineHelper($trainTrip->line);
                    $this->rideInstruction->addWaitLine(new WaitLineIntermediate($waitLine->getLine(),
                        $waitLine->getTrip(),$waitLine->getTransportModeId(),$waitLine->getDuration(),$waitLine->getDestination(),
                        true,$lineHelper->hasPerturbations()));
                    }
                }
            }
        }
    }



    private function tripContainsSection (CommonSection $section,$startId,$endId,$stations)
    {
        $sO1 = $this->getStationOrder($stations,$section->station1_id);
        $sO2 = $this->getStationOrder($stations,$section->station2_id);
        $iO1 = $this->getStationOrder($stations,$startId);
        $iO2 = $this->getStationOrder($stations,$endId);
        return ($iO1>=$sO1&&$iO2<=$sO2);
    }


    //TODO optimize
    private function getStationOrder ($stations,$stationId)
    {
        $i=0;
        foreach ($stations as $station)
        {
            if ($station->id == $stationId)
                return $i;
            $i++;
        }
        return -1;
    }

    private function getCommonSections ($trip1,$trip2)
    {
        $commonSections1 = $trip1->commonSections;
        $commonSections2 = $trip2->commonSections;
        $commonSections = array();
        foreach ($commonSections1 as $commonSection1)
        {
            foreach ($commonSections2 as $commonSection2)
            {
                if ($commonSection1->id==$commonSection2->id)
                {
                    array_push($commonSections,$commonSection1);
                }
            }
        }
        return $commonSections;
    }


}