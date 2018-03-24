<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 24/03/2018
 * Time: 20:21
 */

namespace App\Http\Controllers\PathFinderApi;


class PathRetreiver
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

    private function addLinesToStack ($lines,$level)
    {
        sort($lines);
        $combinations = $this->getPossibleCombinations($lines);
        foreach ($combinations as $combination)
        {
            if (count($combination)!=0&&!in_array($combination,$this->closed)&&!in_array($combination,$this->toBlacklist))
            {
                array_push($this->toBlacklist,$combination);
                array_push($this->levels,$level);
            }
        }
    }

    public function getPaths ($maxLevel)
    {
        $paths = array();
        $next = array();
        $level = 1;
        do
        {
            $this->attributes['transportLineUnused'] = $next;
            $result = \PathFinder::findPath($this->attributes);
            $path = $result[0];
            array_push($paths,$path);
            if (!PathUtils::isPathOnlyWalking($path))
            {
                if ($level<$maxLevel)
                    $this->addLinesToStack(PathUtils::getLinesInPath($path),$level);
                if (count($this->toBlacklist)!=0)
                {
                    $next = array_pop($this->toBlacklist);
                    $level = array_pop($this->levels)+1;
                    array_push($this->closed,$next);
                }
            }
        } while (count($this->toBlacklist)!=0);
        return array_unique($paths,SORT_REGULAR);
    }

    public static function getAllPaths ($attributes,$maxLevel)
    {
        $pathRetreiver = new PathRetreiver($attributes);
        return $pathRetreiver->getPaths($maxLevel);
    }

}