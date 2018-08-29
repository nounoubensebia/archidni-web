<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 28/08/2018
 * Time: 19:01
 */

namespace App\Http\Controllers\OtpPathFinder;


use App\GeoUtils;
use App\Http\Controllers\PathFinderApi\Polyline;

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
        $content = [];
        $content['timeout'] = 50;
        $content['entries'] = $this->originsDestinations;
        $content = json_encode($content);
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
            if (!isset($path->originDestinationEntry))
            {
                echo $content;
                exit();
                break;
            }
            $originDestinationEntry = $path->originDestinationEntry;
            $origin = new Coordinate($originDestinationEntry->origin->latitude,$originDestinationEntry->origin->longitude);
            $destination = new Coordinate($originDestinationEntry->destination->latitude,$originDestinationEntry->destination->longitude);
            if (isset($path->polyline))
                $polyline = $path->polyline;
            else
                $polyline = Polyline::encodeCoord([$origin,$destination]);
            array_push($walkingEntries,new WalkingCacheEntry($origin,$destination,$polyline));
        }
        return $walkingEntries;
    }

}