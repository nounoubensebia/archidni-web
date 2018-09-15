<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 28/08/2018
 * Time: 23:58
 */

namespace App\Http\Controllers\OtpPathFinder;


use App\Http\Controllers\OtpPathFinder\PathInstruction\RideInstructionIntermediate;
use App\Http\Controllers\OtpPathFinder\PathInstruction\WaitLineIntermediate;
use App\Http\Controllers\OtpPathFinder\PathInstruction\WalkInstruction;
use App\TrainTrip;

class OtpIntermediateToOutputPathFormatter
{
    /**
     * @var OtpPathIntermediate
     */
    private $otpIntermediatePath;

    /**
     * OtpIntermediateToOutputPathFormatter constructor.
     * @param OtpPathIntermediate $otpIntermediatePath
     */
    public function __construct(OtpPathIntermediate $otpIntermediatePath)
    {
        $this->otpIntermediatePath = $otpIntermediatePath;
    }


    public function formatPath ()
    {
        $instructions = [];
        $i=0;
        foreach ($this->otpIntermediatePath->getInstructions() as $instruction)
        {
            $newInstruction = [];
            if ($instruction instanceof WalkInstruction)
            {
                $newInstruction['type'] = "walk_instruction";
                $newInstruction['duration'] = $instruction->getDuration();
                $newInstruction['polyline'] = $instruction->getPolyline();
                $newInstruction['destination'] = $instruction->getDestinationName();
                $newInstruction['destination_type'] = ($i==count($this->otpIntermediatePath->getInstructions())-1)?"user_destination":"station";
                array_push($instructions,$newInstruction);
            }
            if ($instruction instanceof RideInstructionIntermediate)
            {
                $newInstruction['type'] = "wait_instruction";
                $newInstruction['coordinate'] = $instruction->getCoordinate();
                $linesArray = [];
                foreach ($instruction->getWaitLinesIntermediate() as $intermediateWaitLine)
                {
                    /**
                     * @var $intermediateWaitLine WaitLineIntermediate
                     */
                    $line = [];
                    $line['id'] = $intermediateWaitLine->getLine()->id;
                    $line['line_name'] = $intermediateWaitLine->getLine()->name;
                    $line['transport_mode_id'] = $intermediateWaitLine->getTransportModeId();
                    $line['duration'] = $intermediateWaitLine->getDuration();
                    $line['destination'] = $intermediateWaitLine->getDestination();
                    $line['exact_waiting_time'] = $intermediateWaitLine->getExactWaitingTime();
                    $line['has_perturbations'] = $intermediateWaitLine->getHasPerturbations();
                    if ($intermediateWaitLine->getTrip() instanceof TrainTrip)
                    {
                        $line['arrival_time'] = $this->getStationTrainArrivalTime($intermediateWaitLine->getTrip(),
                            $instruction->getStationStartId());
                    }
                    array_push($linesArray,$line);
                }
                $newInstruction['lines'] = $linesArray;
                array_push($instructions,$newInstruction);
                $newInstruction = [];
                $newInstruction['type'] = "ride_instruction";
                $newInstruction['transport_mode_id'] = $intermediateWaitLine->getTransportModeId();
                $newInstruction['stations'] = $instruction->getStations();
                $newInstruction['polyline'] = $instruction->getPolyline();
                $newInstruction['duration'] = $instruction->getRideDuration();
                $newInstruction['error_margin'] = $instruction->getErrorMargin();
                array_push($instructions,$newInstruction);
            }
            $i++;
        }
        return $instructions;
    }

    private function getStationTrainArrivalTime ($trip,$startStationId)
    {
        foreach ($trip->stations as $station)
        {
            if ($station->id==$startStationId)
            {
                return Utils::getSecondsSinceMidnight(
                    $trip->departures->first()->time)+$station->pivot->minutes*60;
            }
        }
        return -1;
    }

}