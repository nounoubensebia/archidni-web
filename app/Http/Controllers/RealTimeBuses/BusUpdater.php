<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 06/10/2018
 * Time: 19:09
 */

namespace App\Http\Controllers\RealTimeBuses;


use App\Bus;
use App\Http\Controllers\Context;
use App\Http\Controllers\OtpPathFinder\Utils;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\DB;

class BusUpdater
{

    /**
     * @var BusUpdaterContext
     */
    private $context;

    /**
     * BusUpdater constructor.
     */
    public function __construct()
    {
        $this->context = new BusUpdaterContext();
    }

    public function updateLocations ()
    {
        try{
            $beforeAll = Utils::getTimeInMilis();
            $before = Utils::getTimeInMilis();
            $response = $this->makeUpdateRequest();
            $after = Utils::getTimeInMilis();
            $this->context->addToDebug("getting_positions",$after-$before);
            $before = Utils::getTimeInMilis();
            $buses = $this->getBusesFromResponse($response);
            $after = Utils::getTimeInMilis();
            $this->context->addToDebug("parsing",$after-$before);
            $before = Utils::getTimeInMilis();
            $this->updateBuses($buses);
            $after = Utils::getTimeInMilis();
            $this->context->addToDebug("updating",$after-$before);
            $afterAll = Utils::getTimeInMilis();
            $this->context->addToDebug("total_time",$afterAll-$beforeAll);
            return ["msg" => "update successful","debug" => $this->context->getDebug()];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function updateBuses ($buses)
    {
        if (count($buses)>0)
            DB::transaction(function () use($buses)
            {
                DB::table("buses")->truncate();
                Bus::insert($buses);
                //DB::table("buses")->insert($buses);
            });
        else
        {
            throw new \Exception("not authorized");
        }

        /*if (count($buses)>0)
        {
            foreach ($buses as $bus)
            {
                Bus::updateOrCreate(
                    ["id" =>$bus["id"]],
                    $bus
                );
            }
        }*/
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

                if (isset($bus->cap))
                {
                    $course = $bus->cap;
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