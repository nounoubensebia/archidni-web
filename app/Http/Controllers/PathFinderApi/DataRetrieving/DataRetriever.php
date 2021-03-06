<?php

/**
 * Created by PhpStorm.
 * User: ressay
 * Date: 09/02/18
 * Time: 13:08
 */

include "InvalidDataFormatException.php";
class DataRetriever
{

    /**
     * attributes to be sent on GET parameters
     * @var array
     */

    static private $attributes = [
        "origin", // latitude and longitude of origin
        "destination", // latitude and longitude of destination
        "time", // time of path searching format hh:mm. default: NOW
        "day", // day of path searching 1 : monday and 7: sunday. default: today
        "transportMeanUsed", // restriction over transport mean by stating used transport means
        // separated by ",". default all
        "transportMeanUnused", // restriction over transport mean by stating unused transport means
        // separated by ",". default none
        // 1=>metro,2=>train,3=>bus,4=>tramway
        "transportLineUsed", // restriction over transport line by stating used transport lines
        // separated by ",". default all
        "transportLineUnused", // restriction over transport line by stating unused transport lines
        // separated by ",". default none
        "MaxWalkingTimePerCorrespondence", // restriction over maximum time passed to walk
        // between two stations, time given in minutes, default infinity
        "MaxWaitingTimePerCorrespondence", // restriction over maximum time passed to waiting
        // in a stations, time given in minutes, default infinity
        "priority", // order found paths by priority : time, distance, price... default time
        "numberOfPaths", // number of paths to return ordered by priority, -1 for maximum number
        //of paths possible. default 1 path
    ];

    static private $necessaryAttributes = [
        "origin", // latitude and longitude of origin
        "destination" // latitude and longitude of destination
    ];

    static private $priorityOptions = [
        "distance",
        "time"
        ];

    public static function retrieve($attribute,$value)
    {
        return self::$attribute($value);
    }

    public static function retrieveAttributes($getAttr)
    {
        $hash = [];
        foreach ($getAttr as $key => $value) {
            if(\DataRetriever::isAnAttribute($key))
                $hash[$key] = \DataRetriever::retrieve($key, $value);
        }
        return $hash;
    }



    public static function allNecessaryAttributesExist($params)
    {
        foreach (self::$necessaryAttributes as $attribute)
        {
            if(!key_exists($attribute,$params))
                return false;
        }
        return true;
    }

    public static function isAnAttribute($attr)
    {
        return in_array($attr,self::$attributes);
    }



    private static function origin($value)
    {
        $result = self::getLatLong($value);
        return $result;
    }

    private static function destination($value)
    {
        $result = self::getLatLong($value);
        return $result;
    }

    private static function time($value)
    {
        if(preg_match("/^(\d\d?):(\d\d?)$/",$value,$tab)
            && $tab[1] >= 0 && $tab[1] < 24
            && $tab[2] >= 0 && $tab[2] < 60)
            return UtilFunctions::strToMin($value);
        else
            throw new InvalidDataFormatException("invalid day format, expected digit hh:mm
             found ".$value);
    }

    private static function day($value)
    {
        if(preg_match("/^\d$/",$value,$tab) && $value >=1 && $value <=7)
            return 1<<($value-1);
        else
            throw new InvalidDataFormatException("invalid day format, expected digit between 0 and 6
             found ".$value);
    }

    private static function MaxWalkingTimePerCorrespondence($value)
    {
        if(preg_match("/^\d+$/",$value))
            return $value;
        else
            throw new InvalidDataFormatException("invalid maximum walking time per correspondence format,
             expected integer found ".$value);
    }

    private static function MaxWaitingTimePerCorrespondence($value)
    {
        if(preg_match("/^\d+$/",$value))
            return $value;
        else
            throw new InvalidDataFormatException("invalid maximum waiting time per correspondence format,
             expected integer found ".$value);
    }

    private static function transportMeanUsed($value)
    {
        return self::retrieveTransportRestriction($value);
    }


    private static function transportMeanUnused($value)
    {
        return self::retrieveTransportRestriction($value);
    }

    private static function transportLineUsed($value)
    {
        return self::retrieveTransportRestriction($value);
    }


    private static function transportLineUnused($value)
    {
        return self::retrieveTransportRestriction($value);
    }

    private static function retrieveTransportRestriction($value)
    {
        if(preg_match("/^(\d(,\d)*)$/",$value,$tab))
        {
            $value = explode(",",$value);
            return $value;
        }
        else
            throw new InvalidDataFormatException("invalid transportMeanUsed format, expected digits between 1 and 4
             separated by ',' found ".$value);
    }

    private static function priority($value)
    {
        if(in_array($value,self::$priorityOptions))
            return $value;
        else
        {
            $options = "";
            foreach (self::$priorityOptions as $priorityOption) {
                $options .= $priorityOption." ";
            }
            throw new InvalidDataFormatException("invalid priority format, expected [ ".$options."] found ".$value);
        }
    }

    private static function numberOfPaths($value)
    {
        if(preg_match("/^\d+$/",$value))
            return $value;
        else
            throw new InvalidDataFormatException("invalid numberOfPaths format, expected integer found ".$value);
    }

    private static function getLatLong($value)
    {
        if(preg_match("/(\d+\.\d+),(\d+\.\d+)/",$value,$tab))
            $hash = [$tab[1],$tab[2]];
        else
            throw new InvalidDataFormatException("invalid latitude/longitude format, expected 
            latitude longitude found ".$value);
        return $hash;
    }
}