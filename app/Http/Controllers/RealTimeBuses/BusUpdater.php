<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 06/10/2018
 * Time: 19:09
 */

namespace App\Http\Controllers\RealTimeBuses;


use App\Bus;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class BusUpdater
{


    /**
     * BusUpdater constructor.
     */
    public function __construct()
    {
    }

    public function updateLocations ()
    {
        try{
            $response = $this->makeUpdateRequest();
            $buses = $this->getBusesFromResponse($response);
            $this->storeBuses($buses);
        } catch (GuzzleException $e) {
            throw $e;
        }
    }

    private function storeBuses ($buses)
    {
        foreach ($buses as $bus)
        {
            Bus::updateOrCreate(
                $bus
            );
        }
    }

    private function makeUpdateRequest ()
    {
        $url = "http://196.46.250.250:85/ETUSA/ETUSATrameService.svc/ETUSARealtime";
        $client = new Client();
        try {
            $req = $client->request('GET', $url, []);
        } catch (GuzzleException $e) {
            throw $e;
        }
        return $req->getBody();
    }

    private function getBusesFromResponse ($response)
    {
        $buses = [];
        $root = json_decode($response);
        $arr = $root->ETUSARealTimeResult;
        if (!is_null($arr))
        {
            foreach ($arr as $bus)
            {

                if (isset($bus->Cap))
                {
                    $course = $bus->Cap;
                }
                else
                {
                    $course = null;
                }
                array_push($buses,[
                   'id' => $bus->BUS,
                    'latitude' => $bus->Longitude,
                    'longitude' => $bus->Latitude,
                    'speed' => $bus->Vitesse,
                    'course' => $course,
                    'time' => Carbon::createFromTimestamp($bus->Temps)->toDateTimeString()
                ]);
            }
            return $buses;
        }
    }

}