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
        $GLOBALS['attachNodeTime'] = 0;
        $GLOBALS['gettingWaitingTimeTotalTime'] = 0;
        $edgesTime = 0;
        $openRemovalTime = 0;
        $getLowerScoreTime = 0;
        $contextUpdateTime = 0;
        $evaluationTime = 0;
        $beforeTime = round(microtime(true) * 1000);;
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
            $before = round(microtime(true) * 1000);
            $current = $this->getLowestHScoreNode($toEvaluate,$hScores);
            $after = round(microtime(true) * 1000);
            $getLowerScoreTime += ($after-$before);
            /** @var $prev Node*/
            $before = round(microtime(true) * 1000);

            if($previous->contains($current))
            {

                $prev = $previous[$current];
                $graph->getDynamicContextUpdater()->updateContext($context,$prev->getEdgeTo($current));

            }
            $after = round(microtime(true) * 1000);
            $contextUpdateTime+= ($after-$before);
            if($current->getTag() == $goal->getTag())
            {
                //echo "Edges time ".$edgesTime."<br>";
                //echo "Total Astar time ".(round(microtime(true) * 1000) - $beforeTime)."<br>";
                //echo "Evaluation Time ".$evaluationTime."<br>";
                //echo "Removal Time ".$openRemovalTime."<br>";
                //echo "Context Update Time".$contextUpdateTime."<br>";
                //echo "GetLowerScoreUpdate Time".$getLowerScoreTime."<br>";
               // echo "attachNodesTime ".$GLOBALS['attachNodeTime']."<br>";
               // echo "gettingWaitTime ".$GLOBALS['gettingWaitingTimeTotalTime']."<br>";
                return $this->getPathFromPrev($current,$previous);
            }

            // remove from open

            $before = round(microtime(true) * 1000);
            foreach ($toEvaluate as $key=>$n)
            {
                if($n->getTag() == $current->getTag()) {
                    unset($toEvaluate[$key]);
                    break;
                }
            }
            $after = round(microtime(true) * 1000);
            $openRemovalTime+= ($after-$before);
            array_push($evaluated,$current); // add to closed
            $before = round(microtime(true) * 1000);
            $edges = $current->getNextNodesEdges($context);
            $after = round(microtime(true) * 1000);
            $edgesTime+= ($after-$before);
//            echo "from current: ".$current->getTag()."<BR>";
            $before = round(microtime(true) * 1000);
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
            $after = round(microtime(true) * 1000);
            $evaluationTime+= ($after - $before);
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