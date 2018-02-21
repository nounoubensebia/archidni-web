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
        return $context;
    }

    /**
     * @param $context DynamicContext
     * @param $edge Edge
     */
    public function updateContext($context, $edge)
    {
        $time = $context->getData("time");
        $context->setData("time",$time+$edge->getData("time"));

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
        /** @var $graph Graph
         * @var $node2 Node
         * @var $station1 GraphStation
         */
        foreach ($graph->getNodes() as $node2) {
            $station2 = $node2->getData("station");
            /** @var $station2 GraphStation */
            if(isset($station2) && $node2->getTag() != $node->getTag()
                && $station1->getTrip()->getLine()->id != $station2->getTrip()->getLine()->id)
            {
                $edgeVal = UtilFunctions::getTime($node->getData("position"),$node2->getData("position"));
                $edge = $graph->attachNodes($node, $node2
                    , $edgeVal*GraphLinker::$byFootPenalty+$station2->getWaitingTime($time + $edgeVal));
                $edge->addData("type", "byFoot");
                $edge->addData("time",$edgeVal);
            }
        }
        if($this->getNextLoader() != null)
            $this->getNextLoader()->loadEdges($context,$node);
    }

}