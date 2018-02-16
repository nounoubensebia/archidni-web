<?php

/**
 * Created by PhpStorm.
 * User: ressay
 * Date: 19/08/17
 * Time: 16:17
 */
class AStar
{
    private $heuristic;

    /**
     * AStar constructor.
     * @param $heuristicFunction
     * @throws Exception
     */
    public function __construct($heuristicFunction)
    {
        if(is_a($heuristicFunction,'HeuristicEstimator'))
            $this->heuristic = $heuristicFunction;
        else
            throw new Exception("AStar constructor requires HeuristicEstimator object");
    }



    public function findPath($start,$goal)
    {
        $evaluated = array(); // closed
        $toEvaluate = array($start); // open
        $previous = new SplObjectStorage();
        $scores = new SplObjectStorage();
        $hScores = new SplObjectStorage();
        $scores[$start] = 0;
        $hScores[$start] = $this->heuristic->run($start,$goal);

        while(count($toEvaluate) > 0) // open is not empty
        {
            $current = $this->getLowestHScoreNode($toEvaluate,$hScores);
            if($current->getTag() == $goal->getTag())
            {
                return $this->getPathFromPrev($current,$previous);
            }

            // remove from open
            foreach ($toEvaluate as $key=>$n)
            {
                if($n->getTag() == $current->getTag()) {
                    unset($toEvaluate[$key]);
                    break;
                }
            }
            array_push($evaluated,$current); // add to closed

            $edges = $current->getOEdges();
            foreach ($edges as $edge)
            {
                $node = ($edge->getNode1()->getTag() == $current->getTag() ? $edge->getNode2() : $edge->getNode1());
                $weight = $edge->getWeight();

                if(!in_array($node,$evaluated,true))
                {
                    if(!in_array($node,$toEvaluate,true))
                        array_push($toEvaluate,$node);

                    $newScore = $scores[$current] + $weight;
                    if(!isset($scores[$node]) || $scores[$node] > $newScore)
                    {
                        $previous[$node] = $current;
                        $scores[$node] = $newScore;
                        $hScores[$node] = $scores[$node] + $this->heuristic->run($node,$goal);
                    }
                }
            }
        }

        return null; // failure

    }

    private function remove($assoTab,$node)
    {
        foreach ($assoTab as $key=>$n)
        {
            if($n->getTag() == $node->getTag()) {
                unset($assoTab[$key]);
                break;
            }
        }
    }

    private function getLowestHScoreNode($toEvaluate,$hScore)
    {
        $selectedNode = null;
        foreach ($toEvaluate as $node)
        {
//            echo "node tag ".$node->getTag()." score: ".(isset($hScore[$node])? $hScore[$node]:"null")."\n";
            if(!isset($min) || (isset($hScore[$node]) && $hScore[$node] < $min))
            {
                $selectedNode = $node;
                $min = $hScore[$node];
            }
        }
        return $selectedNode;
    }

    private function getPathFromPrev($current,$previous)
    {
        $path = array($current);
        while ($previous->contains($current))
        {
            $current = $previous[$current];
            array_push($path,$current);
        }
        return array_reverse($path);
    }

}