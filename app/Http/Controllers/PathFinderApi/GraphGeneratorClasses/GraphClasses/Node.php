<?php

include "Edge.php";
/**
 * Created by PhpStorm.
 * User: ressay
 * Date: 19/08/17
 * Time: 16:19
 */
class Node
{
    private $Oedges;
    private $Iedges;
    private $tag;
    private $heuristicData;
    private $data;
    /** @var  $dynamicEdgeLoader DynamicEdgeLoader */
    private $dynamicEdgeLoader;

    /**
     * @return int
     */
    public function getHeuristicData()
    {
        return $this->heuristicData;
    }

    /**
     * Node constructor.
     * @param $tag
     * @param int $heuristicData
     * @param null $data
     */
    public function __construct($tag,$heuristicData=0,$data=null)
    {
        $this->tag = $tag;
        $this->heuristicData = $heuristicData;
        $this->Oedges = array();
        $this->Iedges = array();
        $this->data = array();
        if($data == null)
            return;
            $keys = array_keys($data);
            foreach ($keys as $key) {
                $this->data[$key] = $data[$key];
            }
    }

    public function getNextNodesEdges($context)
    {
        // loads dynamic edges
        if($this->getDynamicEdgeLoader() != null)
            $this->getDynamicEdgeLoader()->loadEdges($context,$this);
        return $this->getOEdges();
    }
    public function getOEdges()
    {
        return $this->Oedges;
    }

    public function getIEdges()
    {
        return $this->Iedges;
    }

    /**
     * @param $node Node
     * @param int $value
     * @return Edge
     */

    public function attachNode($node, $value = 0)
    {
        $e = new Edge($this,$node,$value);
        $this->addOEdge($e);
        $node->addIEdge($e);
        return $e;
    }

    private function addOEdge($edge)
    {
        array_push($this->Oedges,$edge);
    }

    private function addIEdge($edge)
    {
        array_push($this->Iedges,$edge);
    }

    public function getAttachedNodes()
    {
        $nodes = [];
        foreach ($this->Oedges as $edge)
        {
                array_push($nodes,$edge->getNode2());
        }
        return $nodes;
    }

    /**
     * @param $node Node
     * @return null
     */

    public function getWeightTo($node)
    {
        $e = $this->getEdgeTo($node);
        return ($e!=null)?$e->getWeight():null;
    }

    /**
     * @param $node Node
     * @return Edge|null
     */

    public function getEdgeTo($node)
    {
        foreach ($this->Oedges as $edge)
        {
            if($edge->getNode2()->getTag() == $node->getTag())
                return $edge;
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param mixed $tag
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
    }

    /**
     * @return DynamicEdgeLoader
     */
    public function getDynamicEdgeLoader()
    {
        return $this->dynamicEdgeLoader;
    }

    /**
     * @param DynamicEdgeLoader $dynamicEdgeLoader
     */
    private function setDynamicEdgeLoader(DynamicEdgeLoader $dynamicEdgeLoader)
    {
        $this->dynamicEdgeLoader = $dynamicEdgeLoader;
    }

    /**
     * @param DynamicEdgeLoader $dynamicEdgeLoader
     */
    public function addDynamicEdgeLoader(DynamicEdgeLoader $dynamicEdgeLoader)
    {
        if($this->getDynamicEdgeLoader() != null)
            $this->getDynamicEdgeLoader()->addLoader($dynamicEdgeLoader);
        else
            $this->setDynamicEdgeLoader($dynamicEdgeLoader);
    }



    /**
     * @param string $key
     * @return mixed
     */
    public function getData($key = "")
    {
        if($key == "")
            return $this->data;
        else if(array_key_exists($key,$this->data))
            return $this->data[$key];
        return null;
    }

    /**
     * @param null $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * adds data to node to retrieve for later use
     * @param $key string with which data can be accessed
     * @param $data mixed
     */

    public function addData($key,$data)
    {
        $this->data[$key] = $data;
//        echo "adding data $key => ".$this->data[$key]."\n";
    }





}