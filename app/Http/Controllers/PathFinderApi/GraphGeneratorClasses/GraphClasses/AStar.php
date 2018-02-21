<?php

/**
 * Created by PhpStorm.
 * User: ressay
 * Date: 19/08/17
 * Time: 16:17
 */
class AStar
{

    /**
     * @var $heuristic HeuristicEstimator
     */
    private $heuristic;


    /**
     * AStar constructor.
     * @param $heuristicFunction HeuristicEstimator
     * @throws Exception
     */
    public function __construct($heuristicFunction)
    {
        if(is_a($heuristicFunction,'HeuristicEstimator'))
            $this->heuristic = $heuristicFunction;
        else
            throw new Exception("AStar constructor requires HeuristicEstimator object");
    }


    /**
     * @param $start Node
     * @param $goal Node
     * @param $graph Graph
     * @return array|null
     */
    public function findPath($start,$goal,$graph)
    {
        $evaluated = array(); // closed
        $toEvaluate = array($start); // open
        $previous = new SplObjectStorage();
        $scores = new SplObjectStorage();
        $hScores = new SplObjectStorage();
        $scores[$start] = 0;
        $hScores[$start] = $this->heuristic->run($start,$goal);
        $context = $graph->getDynamicContextUpdater()->createContext();

        while(count($toEvaluate) > 0) // open is not empty
        {
            /** @var $current Node*/
            $current = $this->getLowestHScoreNode($toEvaluate,$hScores);
            /** @var $prev Node*/

            if($previous->contains($current))
            {
                $prev = $previous[$current];
                $graph->getDynamicContextUpdater()->updateContext($context,$prev->getEdgeTo($current));
            }
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

            $edges = $current->getNextNodesEdges($context);
//            echo "from current: ".$current->getTag()."<BR>";
            foreach ($edges as $edge)
            {
                /** @var $node Node
                 * @var $edge Edge
                 */
                $node = ($edge->getNode1()->getTag() == $current->getTag() ? $edge->getNode2() : $edge->getNode1());
                $weight = $edge->getWeight();
//                echo "access: ".$node->getTag()." with weight: $weight <BR>";
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

    /**
     * @param $toEvaluate
     * @param $hScore
     * @return Node
     */
    private function getLowestHScoreNode($toEvaluate,$hScore)
    {
        $selectedNode = null;
        foreach ($toEvaluate as $node)
        {
            /** @var $node Node */
//            echo "node tag ".$node->getTag()." score: ".(isset($hScore[$node])? $hScore[$node]:"null")."\n";
            if(!isset($min) || (isset($hScore[$node]) && $hScore[$node] < $min))
            {
                $selectedNode = $node;
                $min = $hScore[$node];
            }
        }
        return $selectedNode;
    }

    /**
     * @param $current Node
     * @param $previous SplObjectStorage
     * @return array
     */

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