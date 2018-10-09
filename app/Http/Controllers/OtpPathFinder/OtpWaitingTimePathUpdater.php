<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 01/09/2018
 * Time: 17:14
 */

namespace App\Http\Controllers\OtpPathFinder;


use App\Http\Controllers\OtpPathFinder\PathInstruction\Instruction;
use App\Http\Controllers\OtpPathFinder\PathInstruction\RideInstructionIntermediate;
use App\Http\Controllers\OtpPathFinder\PathInstruction\WaitLineIntermediate;
use App\Http\Controllers\OtpPathFinder\PathInstruction\WalkInstruction;
use App\MetroTrip;
use App\TrainTrip;

class OtpWaitingTimePathUpdater
{
    /**
     * @var PathFinderContext
     */
    private $context;



    /**
     * OtpPathUpdater constructor.
     * @param PathFinderContext $context
     */
    public function __construct(PathFinderContext $context)
    {
        $this->context = $context;
    }


    public function updateWaitTimes (OtpPathIntermediate $path)
    {
        $before = Utils::getTimeInMilis();
        $currentTime = Utils::getSecondsSinceMidnight($this->context->getPathFinderAttributes()->getTime().":00");
        $instructions = $path->getInstructions();
        if ($this->context->getPathFinderAttributes()->getArriveBy())
        {
            $instructions = array_reverse($instructions);
        }
        foreach ($instructions as $instruction)
        {

            if ($instruction instanceof RideInstructionIntermediate)
            {
                if (!$this->context->getPathFinderAttributes()->getArriveBy())
                {
                    $this->updateWaitLinesDuration($instruction->getWaitLinesIntermediate(),$instruction,$currentTime);
                    $currentTime = $this->getUpdatedCurrentTime($instruction->getWaitLinesIntermediate(),$currentTime);
                    $currentTime = $this->getUpdatedCurrentTime($instruction,$currentTime);
                }
                else
                {
                    $currentTime = $this->getUpdatedCurrentTime($instruction,$currentTime);
                    $this->updateWaitLinesDuration($instruction->getWaitLinesIntermediate(),$instruction,$currentTime);
                    $currentTime = $this->getUpdatedCurrentTime($instruction->getWaitLinesIntermediate(),$currentTime);
                }
            }
            else
            {
                $currentTime = $this->getUpdatedCurrentTime($instruction,$currentTime);
            }

        }
        $after = Utils::getTimeInMilis();
        $this->context->incrementValue("updating_wait_times",($after-$before));
    }

    /**
     * @param $completedInstruction Instruction|array
     * @param $currentTime integer current reached time in seconds
     */
    private function getUpdatedCurrentTime ($completedInstruction, $currentTime)
    {

        if ($completedInstruction instanceof WalkInstruction)
        {
            $duration =  (int)($completedInstruction->getDuration()*60);
        }
        else {
            if ($completedInstruction instanceof RideInstructionIntermediate)
            {
                $duration = (int)(($completedInstruction->getRideDuration()
                        +$completedInstruction->getRideDuration()*$completedInstruction->getErrorMargin())*60);
            }
            else
                {
                    /**
                     * @var $completedInstruction array
                     */
                    $duration = $this->getWaitLinesDuration($completedInstruction)*60;
                }
            }
        if (!$this->context->getPathFinderAttributes()->getArriveBy())
        {
            $currentTime+=$duration;
        }
        else
        {
            $currentTime-=$duration;
        }

        return $currentTime;
    }

    private function getWaitLinesDuration ($waitLinesArray)
    {
        $min = 10000000;
        foreach ($waitLinesArray as $item)
        {
            /**
             * @var $item WaitLineIntermediate
             */
            if ($item->getDuration()<$min)
            {
                $min = $item->getDuration();
            }
        }
        return $min;
    }


    private function updateWaitLinesDuration ($waitLines,RideInstructionIntermediate $instruction,$currentTime)
    {
        foreach ($waitLines as $waitLine)
        {
            /**
             * @var $waitLine WaitLineIntermediate
             */
            if ($waitLine->getTrip() instanceof MetroTrip)
            {
                $waitTime = $this->getWaitingTimeMetroTrip($waitLine->getTrip(),$currentTime);
            }
            else
            {
                $waitTime = $this->getWaitingTimeOfStationTrain($waitLine->getTrip(),$instruction->getStationStartId(),
                    $currentTime);
            }
            $waitLine->setDuration($waitTime);
        }
    }


    private function getWaitingTimeMetroTrip ($trip,$currentTime)
    {
        $minStartT = 24*60*60;
        $minWaitingTime = 24*60*60;
        foreach ($trip->timePeriods as $timePeriod)
        {
            $t = $currentTime;
            $endT = Utils::getSecondsSinceMidnight($timePeriod->end);
            $strtT = Utils::getSecondsSinceMidnight($timePeriod->start);
            if($t <= $endT && $t >= $strtT)
            {
                return $timePeriod->waiting_time;
            }
            else
            {
                if($strtT > $t)
                {
                    $minWaitingTime = ($minStartT<$strtT-$t)?$minWaitingTime:$timePeriod->waiting_time*60;
                    $minStartT = ($minStartT<($strtT-$t))?$minStartT:($strtT-$t);
                }
            }
        }
        return (($minStartT+$minWaitingTime)/60);
    }

    private function getWaitingTimeOfStationTrain($trip,$stationId,$time)
    {
        // TODO check
        $minWaitingTime = 24*60*60;
        foreach ($trip->departures as $departure) {
            $depTime = Utils::getSecondsSinceMidnight($departure->time)+$this->getStationMinutes($stationId,$trip)*60;
            if($time < $depTime && $minWaitingTime > $depTime-$time)
                $minWaitingTime = $depTime-$time;
        }
        return $minWaitingTime/60;
    }

    private function getStationMinutes ($stationId,$trip)
    {
        foreach ($trip->stations as $station)
        {
            if ($station->id==$stationId)
            {
                return $station->pivot->minutes;
            }
        }
        return null;
    }

    private static function minA($a,$b){return ($a<$b)?$a:$b;}

}