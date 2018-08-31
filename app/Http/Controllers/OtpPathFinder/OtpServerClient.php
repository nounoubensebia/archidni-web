<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 31/08/2018
 * Time: 17:50
 */

namespace App\Http\Controllers\OtpPathFinder;


class OtpServerClient
{
    static $SERVER_URL = "http://localhost:8080/OTPpath";
    private $pathFinderAttributes;

    /**
     * OtpServerClient constructor.
     * @param $pathFinderAttributes
     */
    public function __construct($pathFinderAttributes)
    {
        $this->pathFinderAttributes = $pathFinderAttributes;
    }


    public function getItineraries ($directWalking,$withoutBus,$numItineraries)
    {
        $url = $this->createTransitPathUrl($directWalking,$withoutBus);
        return $this->getItinerariesFromJson(file_get_contents($url."&numItineraries=".$numItineraries));
    }

    private function getItinerariesFromJson ($json)
    {
        $before = round(microtime(true) * 1000);
        $root = json_decode($json);
        $after = round(microtime(true) * 1000);
        $pathResponse = $root->response;

        if (!isset($pathResponse->error))
        {
            $plan = $pathResponse->plan;
            if (isset($plan->itineraries))
                $itineraries = $plan->itineraries;
            else
                $itineraries = $plan->itinerary;
            return $itineraries;
        }
        else
        {
            return [];
        }
    }

    private function createTransitPathUrl ($directWalking, $withoutBus)
    {
        /**
         * @var $attributes PathFinderAttributes
         */
        $attributes = $this->pathFinderAttributes;
        $origin = $attributes->getOrigin();
        $destination = $attributes->getDestination();
        $date = $attributes->getDate();
        $time = $attributes->getTime();
        $originStr = $origin->getLatitude().",".$origin->getLongitude();
        $destinationStr = $destination->getLatitude().",".$destination->getLongitude();
        $directWalking = ($directWalking) ? "true" : "false";
        $withoutBus = ($withoutBus) ? "true" : "false";
        $arriveBy = ($attributes->getArriveBy()) ? "true" : "false";
        return "http://localhost:8080/OTPpath?origin=$originStr&destination=$destinationStr&date=$date"."&time=".$time.
            "&arriveBy=".$attributes->getArriveBy()."&directWalking=".$directWalking."&withoutBus=".$withoutBus;
    }
}