<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BusLinesUpdater\ExcelFilesGenerator;
use App\Http\Controllers\BusLinesUpdater\GeolocToTempConverter;
use App\Http\Controllers\BusLinesUpdater\TempToProductionConverter;
use Illuminate\Http\Request;

class BusLinesUpdaterController extends Controller
{

    public function cleanDatabase ()
    {
        $converter = new TempToProductionConverter();
        $converter->cleanDatabase();
    }

    public function generateExcel ()
    {
        $generator = new ExcelFilesGenerator();
        $generator->generateGeolocExcel();
        $generator->generateTempExcel();
    }

    //
    public function convertGeolocToTemp ()
    {
        $converter = new GeolocToTempConverter();
        return $converter->convertFromGeoloc();
    }

    public function convertProductionToTemp ()
    {
        $converter = new GeolocToTempConverter();
        return $converter->convertFromProduction();
    }

    public function convertTempToProduction ()
    {
        $converter = new TempToProductionConverter();
        $converter->convert();
    }

    public function diag ()
    {
        $converter = new GeolocToTempConverter();
        return $converter->diag();
    }
}
