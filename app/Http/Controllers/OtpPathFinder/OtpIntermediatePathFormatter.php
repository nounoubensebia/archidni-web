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
    /**
     * @var Context
     */
    private $context;
    private $PathFinderAttributes;

    /**
     * OtpIntermediatePathFormatter constructor.
     * @param Context $context
     * @param $PathFinderAttributes
     * @param $json
     */
    public function __construct(Context $context, $PathFinderAttributes)
    {
        $this->context = $context;
        $this->PathFinderAttributes = $PathFinderAttributes;
    }


    public function getFormattedPaths ($itineraries)
    {
        $paths = [];
        foreach ($itineraries as $itinerary)
        {
            //TODO remove direct walking
            $pathBuilder = new OtpIntermediatePathBuilder($this->context,false,$itinerary,$this->PathFinderAttributes);
            $path = $pathBuilder->buildIntermediatePath();
            array_push($paths,$path);
        }
        return $paths;
    }


}