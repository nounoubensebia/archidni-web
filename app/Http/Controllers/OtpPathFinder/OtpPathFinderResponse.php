<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 29/08/2018
 * Time: 23:21
 */

namespace App\Http\Controllers\OtpPathFinder;


class OtpPathFinderResponse implements \JsonSerializable
{
    private $paths;
    private $debug;

    /**
     * OtpPathFinderResponse constructor.
     * @param $paths
     * @param $debug
     */
    public function __construct($paths, $debug)
    {
        $this->paths = $paths;
        $this->debug = $debug;
    }

    /**
     * @return mixed
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * @return mixed
     */
    public function getDebug()
    {
        return $this->debug;
    }

    public function jsonSerialize()
    {
        $var = get_object_vars($this);
        foreach ($var as &$value) {
            if (is_object($value) && method_exists($value,'getJsonData')) {
                $value = $value->getJsonData();
            }
        }
        return $var;
    }

}