<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 31/08/2018
 * Time: 14:34
 */

namespace App\Http\Controllers\OtpPathFinder\DataLoader;


use App\Http\Controllers\OtpPathFinder\Context;
use App\Http\Controllers\OtpPathFinder\Utils;
use App\MetroTrip;
use App\TrainTrip;

class PathsDataLoader
{
    /**
     * @var Context
     */
    private $context;

    /**
     * PathsDataLoader constructor.
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }


    public function loadData ($paths)
    {
        $metroTripsIds = [];
        $trainTripsIds = [];
        // getting trip ids
        foreach ($paths as $path)
        {
            foreach ($path->legs as $leg)
            {
                if (strcmp($leg->mode,"WALK")!=0)
                {
                    $tripId = Utils::getId($leg->tripId);
                    if (Utils::strContains("m",$tripId))
                    {
                        //trip is metro trip
                        $tripId = substr($tripId, 1);
                        if (!in_array($tripId,$metroTripsIds))
                        {
                            array_push($metroTripsIds,$tripId);
                        }
                    }
                    else
                    {
                        //trip is train trip
                        $tripId = substr($tripId, 1);
                        if (!in_array($tripId,$trainTripsIds))
                        {
                            array_push($trainTripsIds,$tripId);
                        }
                    }
                }
            }
        }

        //loading trips
        $metroTrips = MetroTrip::with('line','timePeriods','commonSections','stations','line.sections',
            'commonSections.metroTrips','commonSections.trainTrips','commonSections.metroTrips.timePeriods',
            'commonSections.trainTrips.departures','commonSections.trainTrips.stations','line.notifications',
            'line.transportMode.notifications','commonSections.metroTrips.line','commonSections.trainTrips.line')->find($metroTripsIds);
        $trainTrips = TrainTrip::with('line','departures','commonSections','stations','line.sections',
            'commonSections.metroTrips','commonSections.trainTrips','commonSections.metroTrips.timePeriods',
            'commonSections.trainTrips.departures','commonSections.trainTrips.stations','line.notifications',
            'line.transportMode.notifications','commonSections.metroTrips.line','commonSections.trainTrips.line')->find($trainTripsIds);
        $info = [];
        $info['metroTrips'] = $metroTrips;
        $info['trainTrips'] = $trainTrips;
        return $info;
    }


}