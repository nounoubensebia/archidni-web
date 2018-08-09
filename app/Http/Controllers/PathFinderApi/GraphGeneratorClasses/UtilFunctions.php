<?php

/**
 * Created by PhpStorm.
 * User: ressay
 * Date: 09/02/18
 * Time: 20:02
 */
class UtilFunctions
{

    static public function strToMin($time)
    {
        return date("G", strtotime($time)) * 60 + date("i", strtotime($time));
    }

    /**
     * method that gets walking time from pos1 to pos2
     * @param $pos1
     * @param $pos2
     * @return float
     */
    static public function getTime($pos1,$pos2)
    {
        return (self::getDistance($pos1,$pos2)*12);
    }

    /**
     * creates a matrix in which rows represents "to nodes" and columns represents "from nodes"
     * so matrix[i][j] is the walking time from node j of "from" array to node i of "to" array
     * @param $froms
     * @param $tos
     * @return array
     */

    static public function getTimes($froms,$tos)
    {
        $matrix = [];
        foreach ($froms as $from)
        {
            $line = [];
            foreach ($tos as $to)
            {
                array_push($line,self::getDistance($from,$to)*18);
            }
            array_push($matrix,$line);
        }
        return $matrix;
    }

    static public function getDistance($pos1,$pos2)
    {
        return self::distance($pos1[0],$pos1[1],$pos2[0],$pos2[1]);
    }

    static public function distance($lat1, $lon1, $lat2, $lon2) {

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;

        $t = $miles * 1.609344;
        if (strcmp($t,"NAN")==0)
            return 0;
        else
            return $t;

    }

    public static function getCurrentTime()
    {
        return date("G:i:s");
    }

    public static function getCurrentDay()
    {
        return 1<<(date("N")-1);
    }
}