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
use App\Line;
use App\TempBusLine;
use App\TempBusStationLocation;

class GeolocToTempConverter
{

    public function convertFromGeoloc ()
    {
        $convertibles = $this->getConvertibleLines();
        $this->addLocations($convertibles);
    }

    public function convertFromProduction ()
    {
        $convertibles = $this->getConvertibleLinesFromProduction();
        return $this->addLocationsFromProduction($convertibles);
    }



    public function diag ()
    {
        return ["complete or incomplete "=>$this->getCompleteLines(),
            "convertibility" => $this->diagConvertibles()];
    }

    private function diagConvertibles ()
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
                array_push($convertibles,['aller' => count($geoAller)-count($tempAller),
                    'retour'=>count($geoRetour)-count($tempRetour),'number' => $tempLine->number]);
            }
        }
        return $convertibles;
    }

    private function addLocationsFromProduction ($convertibles)
    {
        foreach ($convertibles as $convertible)
        {
            if ($convertible['aller']==true)
            {
                $prodStatoins = BusLinesUpdaterUtils::getAllerProductionStations($convertible['production']);
                $tempStations = BusLinesUpdaterUtils::getAllerStations($convertible['temp']);
                $this->bindLocationsFromProductionStations($prodStatoins,$tempStations);
            }
        }
    }

    private function bindLocationsFromProductionStations ($productionStations,$tempStations)
    {
        $i=0;
        foreach ($tempStations as $tempStation)
        {
            $productionStation = $productionStations[$i];
            $location = new TempBusStationLocation(['station_id' => $tempStation->id,'latitude' => $productionStation->latitude,
                'longitude' => $productionStation->longitude,'is_verified' => 0,'arrival' => 1]);
            $i++;
            /*if (!$this->tempLocationForStationExists($tempStation))
                $location->save();
            else
            {

            }*/
            if ($tempStation->locations->count()>0)
            {
                foreach ($tempStation->locations as $newlocation)
                {
                    if ($newlocation->arrival==1)
                    {
                        break;
                    }
                    else
                    {
                        $newlocation->delete();
                        $location->save();
                        break;
                    }
                }
            }
            else
                $location->save();
        }
    }

    private function getConvertibleLinesFromProduction ()
    {
        $convertibles = [];
        $productionLines = Line::all();
        $tempLines = TempBusLine::all();
        foreach ($productionLines as $productionLine)
        {
            $tempLine = $this->getTempLineByNumber($productionLine->number,$tempLines);
            if (isset($tempLine))
            {
                $allerProduction = BusLinesUpdaterUtils::getAllerProductionStations($productionLine);
                $allerTemp = BusLinesUpdaterUtils::getAllerStations($tempLine);
                $retourProduction = BusLinesUpdaterUtils::getRetourProductionStations($productionLine);
                $retourTemp = BusLinesUpdaterUtils::getRetourStations($tempLine);
                $aller = (count($allerTemp)==count($allerProduction))? true:false;
                $retour = (count($retourTemp)==count($retourProduction))? true:false;
                array_push($convertibles,['aller' => $aller,'retour'=>$retour,'production' =>$productionLine ,
                    'temp' => $tempLine]);
            }
        }
        return $convertibles;
    }

    public function getCompleteLines ()
    {
        $tempLines = TempBusLine::all();
        $completeLines = [];
        $incompleteLines = [];
        foreach ($tempLines as $tempLine)
        {
            if ($this->isLineComplete($tempLine))
            {
                array_push($completeLines,$tempLine);
            }
            else
            {
                array_push($incompleteLines,["number" => $tempLine->number,"state"=>
                $this->getIncompleteStationsCount($tempLine)]);
            }
        }
        return ['complete lines' => $completeLines,'incomplete_lines' => $incompleteLines];
    }

    private function getIncompleteStationsCount ($tempLine)
    {
        $aller = BusLinesUpdaterUtils::getAllerStations($tempLine);
        $retour = BusLinesUpdaterUtils::getRetourStations($tempLine);
        $a = 0;
        $r = 0;
        foreach ($aller as $item)
        {
            if (BusLinesUpdaterUtils::getTempStationLocation($item)==null)
            {
                $a++;
            }
        }
        foreach ($retour as $item)
        {
            if (BusLinesUpdaterUtils::getTempStationLocation($item)==null)
            {
                $r++;
            }
        }
        return ["aller" => $a,"retour" => $r];
    }

    private function isLineComplete ($tempLine)
    {
        $aller = BusLinesUpdaterUtils::getAllerStations($tempLine);
        $retour = BusLinesUpdaterUtils::getRetourStations($tempLine);
        foreach ($aller as $item)
        {
            if (BusLinesUpdaterUtils::getTempStationLocation($item)==null)
            {
                return false;
            }
        }
        foreach ($retour as $item)
        {
            if (BusLinesUpdaterUtils::getTempStationLocation($item)==null)
            {
                return false;
            }
        }
        return true;
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