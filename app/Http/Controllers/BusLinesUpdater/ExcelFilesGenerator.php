<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 02/11/2018
 * Time: 20:15
 */

namespace App\Http\Controllers\BusLinesUpdater;

use App\GeolocLine;
use App\TempBusLine;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelFilesGenerator
{

    public function generateTempExcel ()
    {
        $tempLines = TempBusLine::all();

        foreach ($tempLines as $tempLine)
        {
            if (BusLinesUpdaterUtils::tempLineExists($tempLine->number))
            {
                $style = array(
                    'alignment' => array(
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'wrap' => true
                    )
                );
                $spreadsheet = new Spreadsheet();
                /*$spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $spreadsheet->getDefaultStyle()->getAlignment()->setWrapText('true');*/
                foreach(range('A','I') as $columnID) {
                    $spreadsheet->getActiveSheet()->getColumnDimension($columnID)
                        ->setAutoSize(true);
                }
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setCellValue('A1', 'code_a');
                $sheet->setCellValue('B1', 'nom_a');
                $sheet->setCellValue('C1', 'code_r');
                $sheet->setCellValue('D1', 'nom_r');

                $allerStations = BusLinesUpdaterUtils::getAllerStations($tempLine);
                $retourStations = BusLinesUpdaterUtils::getRetourStations($tempLine);
                $i=2;
                foreach ($allerStations as $allerStation)
                {
                    $sheet->setCellValue('A'.$i,$allerStation->aotua_id);
                    $sheet->setCellValue('B'.$i,$allerStation->name);
                    $i++;
                }
                $i=2;
                foreach ($retourStations as $retourStation)
                {
                    $sheet->setCellValue('C'.$i,$retourStation->aotua_id);
                    $sheet->setCellValue('D'.$i,$retourStation->name);
                    $i++;
                }

                $writer = new Xlsx($spreadsheet);
                $writer->save('nouveau-ligne '.$tempLine->number.".xlsx");
            }
        }
    }

    public function generateGeolocExcel ()
    {

        $geoLocLines = GeolocLine::all();

        foreach ($geoLocLines as $geoLocLine)
        {
            if (BusLinesUpdaterUtils::tempLineExists($geoLocLine->number))
            {
                $style = array(
                    'alignment' => array(
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'wrap' => true
                    )
                );
                $spreadsheet = new Spreadsheet();
                /*$spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $spreadsheet->getDefaultStyle()->getAlignment()->setWrapText('true');*/
                foreach(range('A','I') as $columnID) {
                    $spreadsheet->getActiveSheet()->getColumnDimension($columnID)
                        ->setAutoSize(true);
                }
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setCellValue('A1', 'id_a');
                $sheet->setCellValue('B1', 'nom_a');
                $sheet->setCellValue('C1', 'code_a (à remplir)');
                $sheet->setCellValue('D1', 'id_r');
                $sheet->setCellValue('E1', 'nom_r');
                $sheet->setCellValue('F1', 'code_r (à remplir)');

                $allerStations = BusLinesUpdaterUtils::getAllerStations($geoLocLine);
                $retourStations = BusLinesUpdaterUtils::getRetourStations($geoLocLine);
                $i=2;
                foreach ($allerStations as $allerStation)
                {
                    $sheet->setCellValue('A'.$i,$allerStation->id);
                    $sheet->setCellValue('B'.$i,$allerStation->name);
                    $i++;
                }
                $i=2;
                foreach ($retourStations as $retourStation)
                {
                    $sheet->setCellValue('D'.$i,$retourStation->id);
                    $sheet->setCellValue('E'.$i,$retourStation->name);
                    $i++;
                }

                $writer = new Xlsx($spreadsheet);
                $writer->save('ancien-ligne '.$geoLocLine->number.".xlsx");
            }
        }


    }
}