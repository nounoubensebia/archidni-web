<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 10/08/2018
 * Time: 19:59
 */

namespace App\Http\Gtfs;


use App\Http\Controllers\OtpPathFinder\Utils;
use App\Line;
use App\MetroTrip;
use App\Operator;
use App\Station;
use App\TimePeriod;
use App\TrainTrip;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use ZipArchive;

class GtfsCreator
{
    private function createAgencyFile ()
    {
        $operators = Operator::all();
        $stringToInsert = "agency_id,agency_name,agency_url,agency_timezone";
        foreach ($operators as $operator)
        {
            $stringToInsert.=PHP_EOL;
            $stringToInsert.=$operator->id.",";
            $stringToInsert.=$operator->name.",";
            $stringToInsert.=$operator->url.",";
            $stringToInsert.="Africa/Algiers";
        }
        $file = fopen("agency.txt","w");
        fwrite($file,$stringToInsert);
        fclose($file);
    }

    private function createStopsFile ()
    {
        $stations = Station::all();
        $stringToInsert = "stop_id,stop_name,stop_lat,stop_lon";
        foreach ($stations as $station)
        {
            $stringToInsert.=PHP_EOL;
            $stringToInsert.=$station->id.",";
            $stringToInsert.=$station->name.",";
            $stringToInsert.=$station->latitude.",";
            $stringToInsert.=$station->longitude;
        }
        $file = fopen("stops.txt","w");
        fwrite($file,$stringToInsert);
        fclose($file);
    }

    private function createRoutesFile ()
    {
        $lines = Line::all();
        $stringToInsert = "agency_id,route_id,route_short_name,route_long_name,route_type";
        foreach ($lines as $line)
        {
            $stringToInsert.=PHP_EOL;
            $stringToInsert.=$line->operator_id.",";
            $stringToInsert.=$line->id.",";
            $shortName = $line->name;
            if ($line->transport_mode_id==3)
            {
                $shortName = $line->number;
            }
            $stringToInsert.=$shortName.",";
            $stringToInsert.=$line->name.",";
            $stringToInsert.=$this->getRouteType($line->transport_mode_id);
        }
        $file = fopen("routes.txt","w");
        fwrite($file,$stringToInsert);
        fclose($file);
    }

    private function createCalendarFile ()
    {
        $trainTrips = TrainTrip::all();
        $metroTrips = MetroTrip::all();
        $stringToInsert = "service_id,monday,tuesday,wednesday,thursday,friday,saturday,sunday,start_date,end_date";
        foreach ($metroTrips as $metroTrip)
        {
            $stringToInsert.=PHP_EOL;
            $stringToInsert.="m".$metroTrip->id.",";
            $stringToInsert.=$this->getDaysString($metroTrip)."20170101,20240101";
        }
        foreach ($trainTrips as $trainTrip)
        {
            $stringToInsert.=PHP_EOL;
            $stringToInsert.="t".$trainTrip->id.",";
            $stringToInsert.=$this->getDaysString($trainTrip)."20170101,20240101";
        }
        $file = fopen("calendar.txt","w");
        fwrite($file,$stringToInsert);
        fclose($file);
    }

    private function getDaysString ($trip)
    {
        $days = $trip->days;
        $string ="";
        if (($days&2)!=0)
        {
            $string.="1,";
        }
        else
        {
            $string.="0,";
        }
        if (($days&4)!=0)
        {
            $string.="1,";
        }
        else
        {
            $string.="0,";
        }
        if (($days&8)!=0)
        {
            $string.="1,";
        }
        else
        {
            $string.="0,";
        }
        if (($days&16)!=0)
        {
            $string.="1,";
        }
        else
        {
            $string.="0,";
        }
        if (($days&32)!=0)
        {
            $string.="1,";
        }
        else
        {
            $string.="0,";
        }
        if (($days&64)!=0)
        {
            $string.="1,";
        }
        else
        {
            $string.="0,";
        }
        if (($days&1)!=0)
        {
            $string.="1,";
        }
        else
        {
            $string.="0,";
        }
        return $string;
    }


    private function createTripsFile ()
    {
        $trainTrips = TrainTrip::all();
        $metroTrips = MetroTrip::all();
        $stringToInsert = "route_id,service_id,trip_id";
        foreach ($metroTrips as $metroTrip)
        {
            $stringToInsert.=PHP_EOL;
            $stringToInsert.=$metroTrip->line_id.",";
            $stringToInsert.="m".$metroTrip->id.",";
            $stringToInsert.="m".$metroTrip->id;
        }

        foreach ($trainTrips as $trainTrip)
        {
            $stringToInsert.=PHP_EOL;
            $stringToInsert.=$trainTrip->line_id.",";
            $stringToInsert.="t".$trainTrip->id.",";
            $stringToInsert.="t".$trainTrip->id;
        }
        $file = fopen("trips.txt","w");
        fwrite($file,$stringToInsert);
        fclose($file);
    }

    private function createStopTimesFile ()
    {
        $metroTrips = MetroTrip::all();
        $stringToInsert = "trip_id,arrival_time,departure_time,stop_id,stop_sequence,timepoint";
        foreach ($metroTrips as $metroTrip)
        {
            $i=0;
            $stations = $metroTrip->stations;
            foreach ($stations as $station)
            {
                $stringToInsert.=PHP_EOL;
                $stringToInsert.="m".$metroTrip->id.",";
                $stringToInsert.=$this->getArrivalTime($station->pivot->minutes).",";
                $stringToInsert.=$this->getArrivalTime($station->pivot->minutes+1).",";
                $stringToInsert.=$station->id.",";
                $stringToInsert.=$i.",";
                $stringToInsert.="0";
                $i++;
            }
        }
        $trainTrips = TrainTrip::all();
        foreach ($trainTrips as $trainTrip)
        {
            $i=0;
            $stations = $trainTrip->stations;
            foreach ($stations as $station)
            {
                $stringToInsert.=PHP_EOL;
                $stringToInsert.="t".$trainTrip->id.",";
                $stringToInsert.=$this->getArrivalTimeTrain($trainTrip,$station->pivot->minutes).",";
                $stringToInsert.=$this->getArrivalTimeTrain($trainTrip,$station->pivot->minutes).",";
                $stringToInsert.=$station->id.",";
                $stringToInsert.=$i.",";
                $stringToInsert.="0";
                $i++;
            }
        }

        $file = fopen("stop_times.txt","w");
        fwrite($file,$stringToInsert);
        fclose($file);
    }

    private function createFrequencyFile ()
    {
        $timePeriods = TimePeriod::all();
        $stringToInsert = "trip_id,start_time,end_time,headway_secs,exact_times";
        foreach ($timePeriods as $period)
        {
            $stringToInsert.=PHP_EOL;
            $stringToInsert.="m".$period->metro_trip_id.",";
            $stringToInsert.=$period->start.",";
            $stringToInsert.=$period->end.",";
            $stringToInsert.=($period->waiting_time*60).",";
            $stringToInsert.="0";
        }
        $file = fopen("frequencies.txt","w");
        fwrite($file,$stringToInsert);
        fclose($file);
    }

    private function getArrivalTimeTrain (TrainTrip $trip,$minutes)
    {
        $departure = $trip->departures->first();
        $departure = Utils::getSecondsSinceMidnight($departure->time);
        return $this->getArrivalTime(($minutes+$departure/60));
    }

    private function getArrivalTime ($minutes)
    {
        $timeHours = (int)($minutes/60);
        $timeMinutes = (int)($minutes - $timeHours*60);
        if ($timeMinutes<10)
            $timeMinutes = "0".$timeMinutes;
        return $timeHours.":".$timeMinutes.":00";
    }

    private function getRouteType ($transport_mode_id)
    {
        if ($transport_mode_id==1)
        {
            return 1;
        }
        if ($transport_mode_id==2)
        {
            return 2;
        }
        if ($transport_mode_id==3)
        {
            return 3;
        }
        if ($transport_mode_id==4)
        {
            return 0;
        }
        if ($transport_mode_id==5)
        {
            return 0;
        }
        return 7;
    }


    public function createGtfsFeed ()
    {
        $this->createAgencyFile();
        $this->createStopsFile();
        $this->createRoutesFile();
        $this->createCalendarFile();
        $this->createTripsFile();
        $this->createStopTimesFile();
        $this->createFrequencyFile();

    }

    public function deployGtfsFeed()
    {
        $this->createGtfsFeed();
        $zip = new ZipArchive();
        if ($zip->open("test.zip", ZipArchive::CREATE)!==TRUE) {
            exit("cannot open zip\n");
        }
        $zip->addFile("agency.txt");
        $zip->addFile('calendar.txt');
        $zip->addFile('stops.txt');
        $zip->addFile('routes.txt');
        $zip->addFile('trips.txt');
        $zip->addFile('stop_times.txt');
        $zip->addFile('frequencies.txt');
        $zip->close();
        $guzzleClient = new Client();
        $request = new Request('POST','http://localhost:8950/storeGtfs',[],
            base64_encode(file_get_contents('test.zip')));
        try {
            $guzzleClient->send($request);
        } catch (GuzzleException $e) {
            print_r($e->getTrace());
        }
    }
}