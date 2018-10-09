<?php
/**
 * Created by PhpStorm.
 * User: noureddine bensebia
 * Date: 09/10/2018
 * Time: 22:12
 */

namespace App\Http\Controllers;


abstract class Context
{
    protected $debug;

    /**
     * Context constructor.
     * @param $debug
     */
    public function __construct($debug)
    {
        $this->debug = $debug;
    }

    public function addToDebug ($key,$value)
    {
        $this->debug["$key"] = $value;
    }

    public function incrementValue ($key,$value)
    {
        if (isset($this->debug[$key]))
            $this->debug[$key] += $value;
        else
            $this->debug[$key] = $value;
    }

    public function pushValue ($key,$value)
    {
        if (isset($this->debug[$key]))
        {
            array_push($this->debug[$key],$value);
        }
        else
        {
            $this->debug[$key] = [];
            array_push($this->debug[$key],$value);
        }
    }

    /**
     * @return array
     */
    public function getDebug()
    {
        return $this->debug;
    }


}