<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 01/09/2018
 * Time: 22:47
 */

namespace App\Http\Controllers\OtpPathFinder;


class OtpPathFilter
{
    /**
     * @var Context
     */
    private $context;

    /**
     * OtpPathFilter constructor.
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    public function getFilteredPaths ($paths)
    {
        $before = Utils::getTimeInMilis();
        $pathsMap = [];
        $i=0;
        foreach ($paths as $path)
        {
            /**
             * @var $path OtpPathIntermediate
             */
            $lines = $path->getPathLines();
            $pathEntry = [];
            $pathEntry['id'] = $i;
            $pathEntry['lines'] = $lines;
            $pathEntry['path'] = $path;
            array_push($pathsMap,$pathEntry);
            $i++;
        }



        //removing duplicates

        for ($i=0;$i<count($pathsMap);$i++)
        {
            for ($j=$i+1;$j<count($pathsMap);$j++)
            {
                if ($this->areSimilar($pathsMap[$i]['lines'],$pathsMap[$j]['lines']))
                {
                    $duration1 = $pathsMap[$i]['path']->getPathDuration();
                    $duration2 = $pathsMap[$j]['path']->getPathDuration();
                    if ($duration1<$duration2)
                    {
                        if (!isset($pathsMap[$i]['admissible'])||$pathsMap[$i]['admissible'])
                            $pathsMap[$i]['admissible'] = true;
                        $pathsMap[$j]['admissible'] = false;
                    }
                    else
                    {
                        $pathsMap[$i]['admissible'] = false;
                        if (!isset($pathsMap[$j]['admissible'])||$pathsMap[$i]['admissible'])
                            $pathsMap[$j]['admissible'] = true;
                    }
                }
            }
        }

        //removing long paths

        $minDuration = 10000000;
        foreach ($pathsMap as $pathEntry)
        {
            if ($pathEntry['path']->getPathDuration()<$minDuration)
            {
                $minDuration = $pathEntry['path']->getPathDuration();
            }
        }

        //echo $minDuration;

        foreach ($pathsMap as &$pathEntry)
        {
            if ($pathEntry['path']->getPathDuration()>=$minDuration*2)
            {
                $pathEntry['admissible'] = false;
            }
        }

        $filteredPaths = [];
        foreach ($pathsMap as $pathEntry)
        {
            if(!isset($pathEntry['admissible'])||$pathEntry['admissible'])
                array_push($filteredPaths,$pathEntry['path']);
        }
        $after = Utils::getTimeInMilis();
        $this->context->incrementValue("filtering_paths",($after-$before));
        return $filteredPaths;
    }

    private function areSimilar($lines1,$lines2)
    {
        if (count($lines1)!=count($lines2))
        {
            return false;
        }
        else
        {
            for($i=0;$i<count($lines1);$i++)
            {
                $found = false;
                foreach ($lines1[$i] as $line1)
                {
                    foreach ($lines2[$i] as $line2)
                    {
                        if ($line1 == $line2)
                        {
                            $found = true;
                            break;
                        }
                        if ($found)
                        {
                            break;
                        }
                    }
                }
                if (!$found)
                {
                    return false;
                }
            }
            return true;
        }
    }

}