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
use App\Http\Controllers\OtpPathFinder\DataLoader\PathDataRetriever;
use App\Http\Controllers\OtpPathFinder\PathInstruction\RideInstructionIntermediate;
use App\Http\Controllers\OtpPathFinder\PathInstruction\WaitLineIntermediate;
use App\Http\Controllers\OtpPathFinder\PathInstruction\WalkInstruction;
use App\Http\Controllers\PathFinderApi\Polyline;
use App\Line;
use App\MetroTrip;
use App\TrainTrip;

class OtpIntermediatePathBuilder
{
    /**
     * @var Context
     */
    private $context;
    private $directWalking;
    private $itinerary;
    /**
     * @var PathDataRetriever
     */
    private $pathDataRetriever;
    /**
     * @var PathFinderAttributes
     */
    private $pathFinderAttributes;

    /**
     * OtpIntermediatePathBuilder constructor.
     * @param $context
     * @param $directWalking
     * @param $itinerary
     * @param PathFinderAttributes $pathFinderAttributes
     */
    public function __construct(Context $context, $directWalking, $itinerary, PathFinderAttributes $pathFinderAttributes)
    {
        $this->context = $context;
        $this->directWalking = $directWalking;
        $this->itinerary = $itinerary;
        $this->pathFinderAttributes = $pathFinderAttributes;
    }

    /**
     * OtpIntermediatePathBuilder constructor.
     * @param $directWalking
     * @param $itinerary
     * @param $pathFinderAttributes
     */


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
        $this->pathDataRetriever = new PathDataRetriever($this->context->getData());
        $instructions = [];
        $i=0;
        foreach ($this->itinerary->legs as $leg)
        {
            $mode = $leg->mode;
            if (strcmp($mode,"WALK")==0)
            {
                $before = Utils::getTimeInMilis();
                array_push($instructions,$this->buildWalkInstruction($leg));
                $after = Utils::getTimeInMilis();
                $this->context->incrementValue("walking_instruction_formatting",$after-$before);
            }
            else
            {
                if ($i==0)
                {
                    array_push($instructions,$this->generateOriginWalkInstruction($leg));
                }
                $before = Utils::getTimeInMilis();
                array_push($instructions,$this->buildWaitInstruction($leg,$this->itinerary));
                $after = Utils::getTimeInMilis();
                $this->context->incrementValue("ride_instruction_formatting",$after-$before);
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
        $instruction = new WalkInstruction($origin,$destination,$instruction['polyline'],$instruction['duration'],"Destination");
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
        $beforeAll = Utils::getTimeInMilis();
        $instruction = [];
        $instruction['type'] = "wait_instruction";
        $waitStation = $leg->from;
        $instruction['coordinate'] = ['latitude' => $waitStation->lat, 'longitude' => $waitStation->lon];
        $lines = [];
        $lineArray = [];
        $before = Utils::getTimeInMilis();
        //$info = $this->getLineTripInfo($leg);
        $info = $this->pathDataRetriever->getLineTripInfo($leg);
        $after = Utils::getTimeInMilis();
        $this->context->incrementValue("getting_line_trip_info",$after-$before);
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
        $before = Utils::getTimeInMilis();
        $lineArray['destination'] = Utils::getTripDestination($trip)->name;
        $after = Utils::getTimeInMilis();
        $this->context->incrementValue("getting_trip_destination",$after-$before);
        $lineArray['exact_waiting_time'] = !$info['is_metro_trip'];
        $lineHelper = new LineHelper($line);
        $before = Utils::getTimeInMilis();
        $lineArray['has_perturbations'] = $lineHelper->hasPerturbations();
        $after = Utils::getTimeInMilis();
        $this->context->incrementValue("getting_alerts",$after-$before);
        $lineObject = new WaitLineIntermediate($line,$trip,$lineArray['transport_mode_id'],$lineArray['duration'],
            $lineArray['destination'],$lineArray['exact_waiting_time'],$lineArray['has_perturbations']);
        array_push($lines,$lineObject);
        $instruction['lines'] = $lines;
        $before = Utils::getTimeInMilis();
        $rideInstruction = $this->buildRideInstruction($leg,$itinerary);
        $after = Utils::getTimeInMilis();
        $this->context->incrementValue("building_ride_inside_wait",$after-$before);
        $instruction = new RideInstructionIntermediate($rideInstruction['polyline'],$rideInstruction['stations'],$rideInstruction['duration'],
            new Coordinate($instruction['coordinate']['latitude'],$instruction['coordinate']['longitude']),
            $rideInstruction['error_margin'],$lines);
        $afterAll = Utils::getTimeInMilis();
        $this->context->incrementValue("wait_instruction_total",($afterAll-$beforeAll));
        return $instruction;
    }

    public function buildRideInstruction ($leg,$itinerary)
    {
        $instruction = [];
        $instruction['type'] = "ride_instruction";
        $info = $this->pathDataRetriever->getLineTripInfo($leg);
        $line = $info['line'];
        $trip = $info['trip'];
        $instruction ['transport_mode_id'] = $line->transport_mode_id;
        //$startId = explode(":",$leg->from->stopId);
        $startId = $this->getId($leg->from->stopId);
        $endId = $this->getId($leg->to->stopId);
       // $instruction['stations'] = Utils::getFormattedStationsIn($startId,$endId,$trip);
        $before = Utils::getTimeInMilis();
        $instruction['stations'] = $this->getStationsFromLeg($leg,$line->transport_mode_id);
        $after = Utils::getTimeInMilis();
        $this->context->incrementValue("getting_stations_building_ride",($after-$before));
        $before = Utils::getTimeInMilis();
        $instruction['polyline'] = Utils::getPolylineFromRideInstruction($this->context,$line,$trip,$instruction['stations']);
        $after = Utils::getTimeInMilis();
        $this->context->incrementValue("getting_polyline_building_ride",($after-$before));
        $before = Utils::getTimeInMilis();
        $instruction['duration'] = Utils::getRideDuration($startId,$endId,$trip);
        $after = Utils::getTimeInMilis();
        $this->context->incrementValue("getting_duration_building_ride",($after-$before));
        $before = Utils::getTimeInMilis();
        $instruction['error_margin'] = 0.2;
        $after = Utils::getTimeInMilis();
        $this->context->incrementValue("getting_error_margin_building_ride",($after-$before));
        return $instruction;
    }

    private function getStationsFromLeg ($leg,$transportMode)
    {
        $stops = $leg->stop;
        $stations = [];
        $station = [];
        $station['id'] = $this->getId($leg->from->stopId);
        $station['coordinate'] = new Coordinate($leg->from->lat,$leg->from->lon);
        $station['name'] = $leg->from->name;
        $station['transport_mode_id'] = $transportMode;
        array_push($stations,$station);
        foreach ($stops as $stop)
        {
            $station = [];
            $station['id'] = $this->getId($stop->stopId);
            $station['coordinate'] = new Coordinate($stop->lat,$stop->lon);
            $station['name'] = $stop->name;
            $station['transport_mode_id'] = $transportMode;
            array_push($stations,$station);
        }
        $station['id'] = $this->getId($leg->to->stopId);
        $station['coordinate'] = new Coordinate($leg->to->lat,$leg->to->lon);
        $station['name'] = $leg->to->name;
        $station['transport_mode_id'] = $transportMode;
        array_push($stations,$station);
        return $stations;
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