<?php

namespace App\Http\Controllers;

use App\Departure;
use App\Http\Controllers\OtpPathFinder\Utils;
use App\TrainTrip;
use Illuminate\Http\Request;

class TripController extends Controller
{
    //
    public function createTrainTrips (Request $request)
    {
        $lineId = $request->input('line_id');
        $tripsCsv = $request->input('trips');
        //echo $tripsCsv;
        //return;
        $this->saveTrainTrips($lineId,$tripsCsv);
    }

    private function saveTrainTrips ($line_id, $tripsCsv)
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $tripsCsv);
        rewind($stream);
        $lines = $this->getCsvLines($stream);
        $stations = $lines[0];

        $trip = null;
        for ($i=1;$i<count($lines)-1;$i++)
        {
            $line = $lines[$i];
            $departureTime = $line[1];
            $departureTimeSeconds = Utils::getSecondsSinceMidnight($departureTime.":00");

            $j=0;
            foreach ($line as $item)
            {
                if ($j>0)
                {
                    if (strcmp($item,"-")!=0)
                    {
                        $minutes = Utils::getSecondsSinceMidnight($item.":00")-$departureTimeSeconds;
                        $minutes = $minutes/60;
                        echo $minutes."<BR>";
                        $trip->stations()->attach($stations[$j],["minutes" => $minutes]);
                    }
                }
                else
                {
                    $trip = new TrainTrip(['days'=>$item,'line_id'=>$line_id,'direction'=>0]);
                    $trip->save();
                    $trip->departures()->create(['time'=>$departureTime]);
                }
                $j++;
            }
        }
    }

    private function getCsvLines ($stream)
    {
        $lines = [];
        while (($line = fgetcsv($stream,0,";")) !== FALSE) {
            array_push($lines,$line);
        }
        return $lines;
    }
}
