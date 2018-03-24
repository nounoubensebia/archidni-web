<?php

/**
 * Created by PhpStorm.
 * User: ressay
 * Date: 21/02/18
 * Time: 21:20
 */

include "GraphClasses/DynamicContextUpdater.php";
include "GraphClasses/DynamicContext.php";
include "GraphClasses/DynamicEdgeLoader.php";

class DynamicTransferContextUpdater extends DynamicContextUpdater
{
    /** @var  $filter GeneratorFilter
     * @var $graph Graph
     */
    private $filter;
    private $graph;

    /**
     * DynamicTransferContextUpdater constructor.
     * @param GeneratorFilter $filter
     * @param $graph
     */
    public function __construct(GeneratorFilter $filter, $graph)
    {
        $this->filter = $filter;
        $this->graph = $graph;
    }


    public function createContext()
    {
        if($this->getNextUpdater() != null)
            $context = $this->getNextUpdater()->createContext();
        else
            $context = new DynamicContext();
        $time = $this->filter->getTime();
        $context->setData("time",$time);
        $context->setData("graph",$this->graph);
        $context->setData("filter",$this->filter);
        return $context;
    }

    /**
     * @param $context DynamicContext
     * @param $edge Edge
     */
    public function updateContext($context, $edge)
    {
        $time = $context->getData("time");
        $node1 = $edge->getNode1();
        $node2 = $edge->getNode2();
        $tNode1 = $node1->getData("ctxTime");
        if(!isset($tNode1)) $node1->addData("ctxTime",$time);
        $newTime = $node1->getData("ctxTime")+$edge->getData("time");
        $node2->addData("ctxTime",$newTime);
        $context->setData("time",$newTime);
        $time = $context->getData("time");
        $n1 = $node1->getTag();
        $n2 = $node2->getTag();
//        echo "updating context time is: $time edge from $n1 to $n2 has: ".$edge->getData("time")." <BR>";
        if($this->getNextUpdater() != null)
            $this->getNextUpdater()->updateContext($context,$edge);
    }

}


class DynamicTransferEdgeLoader extends DynamicEdgeLoader
{
    public function loadEdges($context, $node)
    {
        $time = $context->getData("time");
        $graph = $context->getData("graph");
        $station1 = $node->getData("station");
        $node->addData("timeAtNode",$time);
        $filter = $context->getData("filter");
        /** @var $graph Graph
         * @var $node2 Node
         * @var $station1 GraphStation
         * @var $filter GeneratorFilter
         */
        foreach ($graph->getNodes() as $node2) {
            $station2 = $node2->getData("station");
            /** @var $station2 GraphStation */
            if(isset($station2) && $node2->getTag() != $node->getTag()
                && $station1->getTrip()->getLine()->id != $station2->getTrip()->getLine()->id)
            {
                $walkingTime = UtilFunctions::getTime($node->getData("position"),$node2->getData("position"));
                $waitingTime = $station2->getWaitingTime($time + $walkingTime);

                if($filter->filterWaitingTimePerCorrespondence($waitingTime)
                && $filter->filterWalkingTimePerCorrespondence($walkingTime)) {
                    $edge = $graph->attachNodes($node, $node2
                        , $walkingTime * GraphLinker::$byFootPenalty + $waitingTime);
                    $edge->addData("type", "byFoot");
                    $edge->addData("time", $walkingTime + $waitingTime);
                }

            }
        }
        if($this->getNextLoader() != null)
            $this->getNextLoader()->loadEdges($context,$node);
    }

}