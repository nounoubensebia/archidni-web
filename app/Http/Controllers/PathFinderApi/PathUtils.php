<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 24/03/2018
 * Time: 20:24
 */

namespace App\Http\Controllers\PathFinderApi;


use App\Line;

class PathUtils
{
    public static function getLinesInPath ($path)
    {
        $lines = array();
        foreach ($path as $instruction)
        {
            if (isset($instruction['idLine'])&&!in_array($instruction['idLine'],$lines))
            {
                array_push($lines,$instruction['idLine']);
            }
        }
        return $lines;
    }

    public static function getTransportMeansInPath ($path)
    {
        $transportModes = array();
        $lines = self::getLinesInPath($path);
        foreach ($lines as $line)
        {
            $lineDb = Line::find($line);
            $transportMode = $lineDb->transport_mode_id;
            if (!in_array($transportMode,$transportModes))
            {
                array_push($transportModes,$transportMode);
            }
        }
        return $transportModes;
    }

    public static function isPathOnlyWalking ($path)
    {
        return (count($path)==2);
    }
}