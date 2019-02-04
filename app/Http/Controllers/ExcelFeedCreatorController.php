<?php

namespace App\Http\Controllers;

use App\GeolocLine;
use App\Http\Controllers\BusLinesUpdater\BusLinesUpdaterUtils;
use App\Line;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelFeedCreatorController extends Controller
{
    //
    public function createBusExcel (Request $request)
    {
        $lines = Line::where('transport_mode_id','=','3')->where('id','<','600')->get();
        foreach ($lines as $line)
        {

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            foreach(range('A','I') as $columnID) {
                $spreadsheet->getActiveSheet()->getColumnDimension($columnID)
                    ->setAutoSize(true);
            }
            $sheet->setCellValue('A1','code_station_aller');
            $sheet->setCellValue('B1','nom_station_aller');
            $sheet->setCellValue('C1','lat_station_aller');
            $sheet->setCellValue('D1','lon_station_aller');
            $sheet->setCellValue('E1','code_station_retour');
            $sheet->setCellValue('F1','nom_station_retour');
            $sheet->setCellValue('G1','lat_station_retour');
            $sheet->setCellValue('H1','lon_station_retour');
            $a_stations = BusLinesUpdaterUtils::getAllerProductionStations($line);
            $r_stations = BusLinesUpdaterUtils::getRetourProductionStations($line);
            $i=2;
            $prevStation = null;
            foreach ($a_stations as $station)
            {
                if ($prevStation==null || $prevStation->id !=$station->id)
                {
                    $sheet->setCellValue('A'.$i,$station->id);
                    $sheet->setCellValue('B'.$i,$station->name);
                    $sheet->setCellValue('C'.$i,$station->latitude);
                    $sheet->setCellValue('D'.$i,$station->longitude);
                    $prevStation = $station;
                }
                $i++;
            }
            $i=2;
            $prevStation = null;
            foreach ($r_stations as $station)
            {
                if ($prevStation==null || $prevStation->id !=$station->id)
                {
                    $sheet->setCellValue('E'.$i,$station->id);
                    $sheet->setCellValue('F'.$i,$station->name);
                    $sheet->setCellValue('G'.$i,$station->latitude);
                    $sheet->setCellValue('H'.$i,$station->longitude);
                    $prevStation = $station;
                }
                $i++;
            }
            $writer = new Xlsx($spreadsheet);
            $writer->save('ligne '.$line->number.".xlsx");
            //return BusLinesUpdaterUtils::getAllerProductionStations($line);
        }

    }

    public function createAfretExcel ()
    {
        $geoloc = GeolocLine::where('number','>',200)->get();
        foreach ($geoloc as $line)
        {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            foreach(range('A','I') as $columnID) {
                $spreadsheet->getActiveSheet()->getColumnDimension($columnID)
                    ->setAutoSize(true);
                $sheet->setCellValue('A1','code_station_aller');
                $sheet->setCellValue('B1','nom_station_aller');
                $sheet->setCellValue('C1','lat_station_aller');
                $sheet->setCellValue('D1','lon_station_aller');
                $sheet->setCellValue('E1','code_station_retour');
                $sheet->setCellValue('F1','nom_station_retour');
                $sheet->setCellValue('G1','lat_station_retour');
                $sheet->setCellValue('H1','lon_station_retour');
                $a_stations = BusLinesUpdaterUtils::getAllerStations($line);
                $r_stations = BusLinesUpdaterUtils::getRetourStations($line);

                $i=2;
                $prevStation = null;

                foreach ($a_stations as $station)
                {
                    if ($prevStation==null || $prevStation->id !=$station->id)
                    {
                        $sheet->setCellValue('A'.$i,$station->id);
                        $sheet->setCellValue('B'.$i,$station->name);
                        if (isset(BusLinesUpdaterUtils::getGeolocStationLocation($station)->latitude))
                        {
                            $sheet->setCellValue('C'.$i,BusLinesUpdaterUtils::getGeolocStationLocation($station)->latitude);
                            $sheet->setCellValue('D'.$i,BusLinesUpdaterUtils::getGeolocStationLocation($station)->longitude);
                        }
                        $prevStation = $station;
                    }
                    $i++;
                }

                $i=2;
                $prevStation = null;
                foreach ($r_stations as $station)
                {
                    if ($prevStation==null || $prevStation->id !=$station->id)
                    {
                        $sheet->setCellValue('E'.$i,$station->id);
                        $sheet->setCellValue('F'.$i,$station->name);
                        $sheet->setCellValue('G'.$i,$station->latitude);
                        $sheet->setCellValue('H'.$i,$station->longitude);
                        $prevStation = $station;
                    }
                    $i++;
                }

                $i=2;
                $prevStation = null;


                foreach ($r_stations as $station)
                {
                    if ($prevStation==null || $prevStation->id !=$station->id)
                    {
                        $sheet->setCellValue('E'.$i,$station->id);
                        $sheet->setCellValue('F'.$i,$station->name);
                        if (isset(BusLinesUpdaterUtils::getGeolocStationLocation($station)->latitude))
                        {
                            $sheet->setCellValue('G'.$i,BusLinesUpdaterUtils::getGeolocStationLocation($station)->latitude);
                            $sheet->setCellValue('H'.$i,BusLinesUpdaterUtils::getGeolocStationLocation($station)->longitude);
                        }
                        $prevStation = $station;
                    }
                    $i++;
                }

                $writer = new Xlsx($spreadsheet);
                $writer->save('ligne '.$line->number.".xlsx");

            }
        }
    }
}
