<?php

/**
 * Created by PhpStorm.
 * User: ressay
 * Date: 19/08/17
 * Time: 16:20
 */

class Graph
{
    private $nodes = [];
    private $tags = [];
    private $map = [];
    /** @var  $dynamicContextUpdater DynamicContextUpdater */
    private $dynamicContextUpdater;

    /**
     * @param $node Node
     * @return Node
     */

    public function addNode($node)
    {
        if($this->containsNode($node->getTag()))
            return $this->getNode($node->getTag());

        array_push($this->tags,$node->getTag());
//        array_push($this->nodes,$node);
        $this->nodes[$node->getTag()] = $node;
        return $node;
    }

    public function attachExistingNodes ($node1,$node2,$value = 0)
    {
        $e = $node1->getEdgeTo($node2);
        if($e!=null && $e->getWeight() <= $value)
            return $e;
        return $node1->attachNode($node2,$value);
    }

    /**
     * @param $node1 Node
     * @param $node2 Node
     * @param int $value
     * @return Edge
     */

    public function attachNodes($node1,$node2,$value=0)
    {
        $node1 = $this->addNode($node1);
        $node2 = $this->addNode($node2);
        $e = $node1->getEdgeTo($node2);
        if($e!=null && $e->getWeight() <= $value)
            return $e;
        return $node1->attachNode($node2,$value);
    }

    /**
     * @return array
     */
    public function getMap(): array
    {
        return $this->map;
    }

    /**
     * @param array $map
     */
    public function setMap(array $map)
    {
        $this->map = $map;
    }



    public function containsNode($tag)
    {
        return in_array($tag,$this->tags);
    }

    /**
     * @return array
     */
    public function getNodes()
    {
        return array_values($this->nodes);
    }

    /**
     * @param $tag
     * @return Node
     */

    public function getNode($tag)
    {
        if($this->containsNode($tag))
            return $this->nodes[$tag];
        else
            return null;
    }

    /**
     * @param $graph Graph
     * @return $this
     */

    public function addSubGraph($graph)
    {
        foreach ($graph->nodes as $node) {
            $this->addNode($node);
        }

        foreach ($graph->nodes as $node) {
            // attached nodes are successors of the node
            foreach ($node->getAttachedNodes() as $attachedNode) {
                $n1 = $this->getNode($node->getTag());
                $n2 = $this->getNode($attachedNode->getTag());
                $this->attachNodes($n1,$n2,$node->getWeightTo($attachedNode));
            }
        }
        return $this;
    }


    /**
     * @return DynamicContextUpdater
     */
    public function getDynamicContextUpdater()
    {
        return $this->dynamicContextUpdater;
    }

    /**
     * @param DynamicContextUpdater $dynamicContextUpdater
     */
    private function setDynamicContextUpdater(DynamicContextUpdater $dynamicContextUpdater)
    {
        $this->dynamicContextUpdater = $dynamicContextUpdater;
    }

    /**
     * @param DynamicContextUpdater $dynamicContextUpdater
     */
    public function addDynamicContextUpdater(DynamicContextUpdater $dynamicContextUpdater)
    {
        if($this->getDynamicContextUpdater() != null)
            $this->getDynamicContextUpdater()->addUpdater($dynamicContextUpdater);
        else
            $this->setDynamicContextUpdater($dynamicContextUpdater);
    }

    public function createNodesStationsMap ()
    {
        $graph = $this;
        $map = array();
        foreach ($graph->getNodes() as $node)
        {
            $station = $node->getData('station');
            if (isset($station))
            {
                $id = $station->getId();
                if (isset($map[$id]))
                {
                    array_push($map[$id],$node);
                }
                else
                {
                    $map[$id] = array();
                    array_push($map[$id],$node);
                }
            }
        }
        $this->map = $map;
    }


}