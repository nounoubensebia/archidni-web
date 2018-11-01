<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 01/11/2018
 * Time: 18:59
 */

namespace App\Http\Controllers\BusLinesUpdater;


use App\GeolocLine;
use App\GeolocStation;
use App\Http\Controllers\DataUpdater\BusLinesUpdater;
use App\TempBusLine;
use App\TempBusStationLocation;

class GeolocToTempConverter
{

    public function convert ()
    {
        $convertibles = $this->getConvertibleLines();
        $this->addLocations($convertibles);
    }


    private function addLocations ($convertibleLines)
    {
        // first pass only lines with exact number of stops
        foreach ($convertibleLines as $convertibleLine)
        {
            $geolocLine = $convertibleLine['geo'];
            $tempLine = $convertibleLine['temp'];
            if ($convertibleLine['aller']==true)
            {
                $geoLocAller = BusLinesUpdaterUtils::getAllerStations($geolocLine);
                $tempAller = BusLinesUpdaterUtils::getAllerStations($tempLine);
                $this->bindLocations($geoLocAller,$tempAller);
            }

            if ($convertibleLine['retour']==true)
            {
                $geoLocRetour = BusLinesUpdaterUtils::getRetourStations($geolocLine);
                $tempRetour = BusLinesUpdaterUtils::getRetourStations($tempLine);
                $this->bindLocations($geoLocRetour,$tempRetour);
            }
        }
    }

    private function bindLocations ($geolocStations,$tempStations)
    {
        $i = 0;
        foreach ($geolocStations as $geolocStation)
        {
            $location = BusLinesUpdaterUtils::getGeolocStationLocation($geolocStation);
            $tempStation = $tempStations[$i];
            if (isset($location)&&!$this->tempLocationForStationExists($tempStation))
            {
                $tempLocation = new TempBusStationLocation(['station_id' => $tempStation->id,'latitude' => $location->latitude,
                    'longitude' => $location->longitude,'is_verified' => 0,'arrival' => 0]);
                $tempLocation->save();
            }
            $i++;
        }
    }

    private function tempLocationForStationExists ($tempStation)
    {
        $locations = $tempStation->locations;
        if (!isset($locations) || $locations->count()<=0)
        {
            return false;
        }
        return true;
    }

    private function getConvertibleLines ()
    {
        $geolocLines = GeolocLine::all();
        $tempLines = TempBusLine::all();
        $convertibles = [];
        foreach ($geolocLines as $geolocLine)
        {
            $tempLine = $this->getTempLineByNumber($geolocLine->number,$tempLines);
            if (isset($tempLine))
            {
                $geoAller = BusLinesUpdaterUtils::getAllerStations($geolocLine);
                $geoRetour = BusLinesUpdaterUtils::getRetourStations($geolocLine);
                $tempAller = BusLinesUpdaterUtils::getAllerStations($tempLine);
                $tempRetour = BusLinesUpdaterUtils::getRetourStations($tempLine);
                $aller = (count($geoAller)==count($tempAller))? true:false;
                $retour = (count($geoRetour)==count($tempRetour))? true:false;
                array_push($convertibles,['aller' => $aller,'retour'=>$retour,'geo' =>$geolocLine ,
                    'temp' => $tempLine]);
            }
        }
        return $convertibles;
    }

    private function getTempLineByNumber ($number,$tempLines)
    {
        foreach ($tempLines as $tempLine)
        {
            if ($tempLine->number == $number)
                return $tempLine;
        }
        return null;
    }

}