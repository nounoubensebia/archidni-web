<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 24/03/2018
 * Time: 20:21
 */

namespace App\Http\Controllers\PathFinderApi;


class PathRetriever
{
    private $attributes;
    private $toBlacklist;
    private $closed;
    private $levels;
    /**
     * PathRetreiver constructor.
     * @param $attributes
     */
    public function __construct($attributes)
    {
        $this->attributes = $attributes;
        $this->toBlacklist = array();
        $this->closed = array();
        $this->levels = array();
    }

    private function getPossibleCombinations($array) {
        $results = array(array( ));
        foreach ($array as $element)
            foreach ($results as $combination)
                array_push($results, array_merge(array($element), $combination));
        return $results;
    }

    private function addLinesToBlacklist ($lines, $level, $prev)
    {
        sort($lines);
        $combinations = $this->getPossibleCombinations($lines);
        foreach ($combinations as $combination)
        {
            $arr_merge = array_merge($prev,$combination);
            sort($arr_merge);
            if (count($combination)!=0&&!in_array($arr_merge,$this->closed)&&!in_array($arr_merge,$this->toBlacklist))
            {
                array_unshift($this->toBlacklist,$arr_merge);
                array_unshift($this->levels,$level);
            }
        }
    }

    public function getPaths ($maxLevel)
    {
        $paths = array();
        $next = array();
        $level = 1;
        $paths_number = 0;
        do
        {
            $this->attributes['transportLineUnused'] = $next;
            $result = \PathFinder::findPath($this->attributes);
            $path = $result[0];
            $paths_number++;
            if (!PathUtils::isPathOnlyWalking($path))
            {
                array_push($paths,$path);
            }
            else
            {
                if (count($paths)==0)
                    array_push($paths,$path);
            }
            if (!PathUtils::isPathOnlyWalking($path))
            {
                if ($level<$maxLevel)
                    $this->addLinesToBlacklist(PathUtils::getLinesInPath($path),$level,$next);

            }
            if (count($this->toBlacklist)!=0)
            {
                $next = array_shift($this->toBlacklist);
                $level = array_shift($this->levels)+1;
                array_unshift($this->closed,$next);
            }
            else
            {
                break;
            }
            if ($paths_number>3)
            {
                break;
            }
        } while (true==true);
        return array_unique($paths,SORT_REGULAR);
    }

    public static function getAllPaths ($attributes,$maxLevel)
    {
        $pathRetreiver = new PathRetriever($attributes);
        return $pathRetreiver->getPaths($maxLevel);
    }


}