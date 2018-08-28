<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 28/08/2018
 * Time: 19:01
 */

namespace App\Http\Controllers\OtpPathFinder;


class WalkingPathFinder
{

    private $originsDestinations;

    /**
     * WalkingPathFinder constructor.
     * @param $originsDestinations
     */
    public function __construct($originsDestinations)
    {
        $this->originsDestinations = $originsDestinations;
    }

    public function getPaths ()
    {
        $url = "http://localhost:8080/getWalkPaths";
        $content = json_encode($this->originsDestinations);
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER,
            array("Content-type: application/json"));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
        $json_response = curl_exec($curl);
        $obj = json_decode($json_response);
        $walkingEntries = array();
        foreach ($obj as $path)
        {
            $originDestinationEntry = $path->originDestinationEntry;
            $origin = new Coordinate($originDestinationEntry->origin->latitude,$originDestinationEntry->origin->longitude);
            $destination = new Coordinate($originDestinationEntry->destination->latitude,$originDestinationEntry->destination->longitude);
            $polyline = $path->polyline;
            array_push($walkingEntries,new WalkingCacheEntry($origin,$destination,$polyline));
        }
        return $walkingEntries;
    }

}