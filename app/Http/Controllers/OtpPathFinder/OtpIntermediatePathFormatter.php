<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 27/08/2018
 * Time: 18:43
 */

namespace App\Http\Controllers\OtpPathFinder;


class OtpIntermediatePathFormatter
{
    private $PathFinderAttributes;
    private $json;

    /**
     * OtpIntermediatePathFormatter constructor.
     * @param $PathFinderAttributes
     * @param $json
     */
    public function __construct($json,$PathFinderAttributes)
    {
        $this->PathFinderAttributes = $PathFinderAttributes;
        $this->json = $json;
    }


    public function getFormattedPaths ()
    {
        $root = json_decode($this->json);
        $pathResponse = $root->response;
        if (!isset($pathResponse->error))
        {
            $plan = $pathResponse->plan;
            if (isset($plan->itineraries))
                $itineraries = $plan->itineraries;
            else
                $itineraries = $plan->itinerary;
            $paths = [];
            foreach ($itineraries as $itinerary)
            {
                $pathBuilder = new OtpIntermediatePathBuilder($root->directWalking,$itinerary,$this->PathFinderAttributes);
                $path = $pathBuilder->buildIntermediatePath();
                array_push($paths,$path);
            }
            return $paths;
        }
        else
        {
            return [];
        }
    }


}