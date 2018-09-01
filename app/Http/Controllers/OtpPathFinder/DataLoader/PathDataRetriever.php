<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 31/08/2018
 * Time: 20:19
 */

namespace App\Http\Controllers\OtpPathFinder\DataLoader;


use App\Http\Controllers\OtpPathFinder\Utils;

class PathDataRetriever
{
    private $data;

    /**
     * PathDataRetriever constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getLineTripInfo ($leg)
    {
        $tripId = Utils::getId($leg->tripId);

        if (Utils::strContains("m",$tripId))
        {
            $tripId = substr($tripId,1);
            $trip = $this->data['metroTrips']->where('id',$tripId)->first();
            $line = $this->data['metroTrips']->where('id',$tripId)->first()->line;
            $info['is_metro_trip'] = true;
        }
        else
        {
            $tripId = substr($tripId,1);
            $trip = $this->data['trainTrips']->where('id',$tripId)->first();
            $line = $this->data['trainTrips']->where('id',$tripId)->first()->line;
            $info['is_metro_trip'] = false;
        }
        $info['line'] = $line;
        $info['trip'] = $trip;
        return $info;
    }

    public function getTrip ($tripId,$isMetroTrip)
    {
        if ($isMetroTrip)
            return $this->data['metroTrips']->where('id',$tripId)->first();
        else
            return $this->data['trainTrips']->where('id',$tripId)->first();
    }

}