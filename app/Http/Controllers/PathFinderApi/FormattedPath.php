<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 09/08/2018
 * Time: 02:06
 */

namespace App\Http\Controllers\PathFinderApi;


class FormattedPath
{
    private $nodes;

    /**
     * FormattedPath constructor.
     * @param $nodes
     */
    public function __construct($nodes)
    {
        $this->nodes = $nodes;
    }

    /**
     * @return mixed
     */
    public function getNodes()
    {
        return $this->nodes;
    }


    public function getDuration()
    {
        $duration = 0;
        foreach ($this->nodes as $node)
        {
            if (strcmp($node['type'],"wait_instruction")==0)
            {
                $duration+=$node['lines'][0]['duration'];
            }
            else
            {
                $duration+=$node['duration'];
            }
        }
        return $duration;
    }

}