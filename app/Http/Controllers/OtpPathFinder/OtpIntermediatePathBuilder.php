<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 27/08/2018
 * Time: 18:06
 */

namespace App\Http\Controllers\OtpPathFinder;


use App\GeoUtils;
use App\Http\Controllers\LineHelper;
use App\Http\Controllers\OtpPathFinder\PathInstruction\RideInstructionIntermediate;
use App\Http\Controllers\OtpPathFinder\PathInstruction\WaitLineIntermediate;
use App\Http\Controllers\OtpPathFinder\PathInstruction\WalkInstruction;
use App\Http\Controllers\PathFinderApi\Polyline;
use App\Line;
use App\MetroTrip;
use App\TrainTrip;

class OtpIntermediatePathBuilder
{

    private $directWalking;
    private $itinerary;
    /**
     * @var PathFinderAttributes
     */
    private $pathFinderAttributes;

    /**
     * OtpIntermediatePathBuilder constructor.
     * @param $directWalking
     * @param $itinerary
     * @param $pathFinderAttributes
     */
    public function __construct($directWalking, $itinerary, $pathFinderAttributes)
    {
        $this->directWalking = $directWalking;
        $this->itinerary = $itinerary;
        $this->pathFinderAttributes = $pathFinderAttributes;
    }

    /**
     * OtpIntermediatePathBuilder constructor.
     * @param $directWalking
     * @param $itinerary
     * @param $time
     * @param $date
     * @param $arriveBy
     * @param $origin
     * @param $destination
     */



    public function buildIntermediatePath()
    {
        $instructions = [];
        $i=0;
        foreach ($this->itinerary->legs as $leg)
        {
            $mode = $leg->mode;
            if (strcmp($mode,"WALK")==0)
            {
                array_push($instructions,$this->buildWalkInstruction($leg));
            }
            else
            {
                if ($i==0)
                {
                    array_push($instructions,$this->generateOriginWalkInstruction($leg));
                }
                array_push($instructions,$this->buildWaitInstruction($leg,$this->itinerary));
                //array_push($instructions,$this->buildRideInstruction($leg,$this->itinerary));
                if ($i==count($this->itinerary->legs)-1)
                {
                    array_push($instructions,$this->generateDestinationInstruction($leg));
                }
            }
            $i++;
        }
        $otpPath =  new OtpPathIntermediate($this->directWalking,$this->pathFinderAttributes,$instructions);
        return $otpPath;
    }

    private function generateDestinationInstruction ($rideLeg)
    {
        $instruction = [];
        $instruction['type'] = "walk_instruction";
        $origin = new Coordinate($rideLeg->to->lat,$rideLeg->to->lon);
        $destination = $this->pathFinderAttributes->getDestination();
        $instruction['duration'] = GeoUtils::getWalkingTimeCoord($origin,$destination);
        $instruction['polyline'] = Polyline::encodeCoord([$origin,$destination]);
        $instruction['destination'] = $this->pathFinderAttributes->getDestination();
        $instruction['destination_type'] = "user_destination";
        $instruction = new WalkInstruction($origin,$destination,$instruction['polyline'],$instruction['duration'],"destination");
        return $instruction;
    }

    private function generateOriginWalkInstruction ($rideLeg)
    {
        $instruction = [];
        $instruction['type'] = "walk_instruction";
        $destination = new Coordinate($rideLeg->from->lat,$rideLeg->from->lon);
        $instruction['duration'] = GeoUtils::getWalkingTimeCoord($this->pathFinderAttributes->getOrigin(),$destination);
        $destinationName = $rideLeg->from->name;
        $instruction['destination'] = $destinationName;
        $instruction['polyline'] = Polyline::encodeCoord([$this->pathFinderAttributes->getOrigin(),$destination]);
        $instruction['destination_type'] = "station";
        $origin = $this->pathFinderAttributes->getOrigin();
        $instruction = new WalkInstruction($origin,$destination,$instruction['polyline'],$instruction['duration'],$instruction['destination']);
        return $instruction;
    }



    public function buildWalkInstruction($leg)
    {
        $endTime = $leg->endTime;
        $startTime = $leg->startTime;
        $duration = Utils::getTimeFromDateObject($endTime)-Utils::getTimeFromDateObject($startTime);
        $duration /=1000;
        $duration /=60;
        $duration = (int) $duration;
        $destinationName = $leg->to->name;
        $origin = new Coordinate($leg->from->lat,$leg->from->lon);
        $destination = new Coordinate($leg->to->lat,$leg->to->lon);
        $instruction = new WalkInstruction($origin,$destination,$leg->legGeometry->points,$duration,$destinationName);
        return $instruction;
    }

    public function buildWaitInstruction ($leg,$itinerary)
    {
        $instruction = [];
        $instruction['type'] = "wait_instruction";
        $waitStation = $leg->from;
        $instruction['coordinate'] = ['latitude' => $waitStation->lat, 'longitude' => $waitStation->lon];
        $lines = [];
        $lineArray = [];
        $info = $this->getLineTripInfo($leg);
        $line = $info['line'];
        $trip = $info['trip'];
        $lineArray['id'] = $line->id;
        $lineArray['line_name'] = $line->name;
        $lineArray['transport_mode_id'] = $line->transport_mode_id;
        if (isset($leg->from->arrival))
            $duration = Utils::getTimeFromDateObject($leg->from->departure) - Utils::getTimeFromDateObject($leg->from->arrival);
        else
        {
            $duration = Utils::getTimeFromDateObject($leg->startTime)- Utils::getTimeFromDateObject($itinerary->startTime);
        }
        $duration /=1000;
        $duration /=60;
        $duration = (int) $duration;
        $lineArray['duration'] = $duration;
        $lineArray['destination'] = Utils::getTripDestination($trip->id,$info['is_metro_trip'])->name;
        $lineArray['exact_waiting_time'] = !$info['is_metro_trip'];
        $lineHelper = new LineHelper($line);
        $lineArray['has_perturbations'] = count($lineHelper->getCurrentAlerts())>0;
        $lineObject = new WaitLineIntermediate($line,$trip,$lineArray['transport_mode_id'],$lineArray['duration'],
            $lineArray['destination'],$lineArray['exact_waiting_time'],$lineArray['has_perturbations']);
        array_push($lines,$lineObject);
        $instruction['lines'] = $lines;
        $rideInstruction = $this->buildRideInstruction($leg,$itinerary);
        $instruction = new RideInstructionIntermediate($rideInstruction['polyline'],$rideInstruction['stations'],$rideInstruction['duration'],
            new Coordinate($instruction['coordinate']['latitude'],$instruction['coordinate']['longitude']),
            $rideInstruction['error_margin'],$lines);
        return $instruction;
    }

    public function buildRideInstruction ($leg,$itinerary)
    {
        $instruction = [];
        $instruction['type'] = "ride_instruction";
        $info = $this->getLineTripInfo($leg);
        $line = $info['line'];
        $trip = $info['trip'];
        $instruction ['transport_mode_id'] = $line->transport_mode_id;
        //$startId = explode(":",$leg->from->stopId);
        $startId = $this->getId($leg->from->stopId);
        $endId = $this->getId($leg->to->stopId);
        $instruction['stations'] = Utils::getFormattedStationsIn($startId,$endId,$trip);
        $instruction['polyline'] = Utils::getPolylineFromRideInstruction($line,$trip,$instruction['stations']);
        $instruction['duration'] = Utils::getRideDuration($startId,$endId,$trip);
        $instruction['error_margin'] = 0.2;
        return $instruction;
    }

    private function getLineTripInfo ($leg)
    {
        $info = [];
        $routeId = $this->getId($leg->routeId);
        $line = Line::find($routeId);
        $tripId = $this->getId($leg->tripId);
        if (Utils::strContains("m",$tripId))
        {
            $tripId = substr($tripId,1);
            $trip = MetroTrip::find($tripId);
            $info['is_metro_trip'] = true;
        }
        else
        {
            $tripId = substr($tripId,1);
            $trip = TrainTrip::find($tripId);
            $info['is_metro_trip'] = true;
        }
        $info['line'] = $line;
        $info['trip'] = $trip;
        return $info;
    }

    private function getId ($idObj)
    {
        if (isset($idObj->agencyId))
        {
            return $idObj->id;
        }
        else
        {
            $routeId = explode(":",$idObj);
            return $idObj[1];
        }
    }

}