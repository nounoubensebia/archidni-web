<?php

/**
 * Created by PhpStorm.
 * User: ressay
 * Date: 16/02/18
 * Time: 11:34
 */
class PathNode
{
    private $name;
    private $latitude;
    private $longitude;
    /** @var $next PathNode */
    private $next;
    private $waitingTimeAtNode;
    private $transportModeToNextNode;

    public function toArray()
    {
        $array = [];
        $array["name"] = $this->getName();
        $array["latitude"] = $this->getLatitude();
        $array["longitude"] = $this->getLongitude();
        $array["waitingTime"] = $this->getWaitingTimeAtNode();
        $array["transportModeToNextNode"] = $this->getTransportModeToNextNode();
        return $array;
    }

    /**
     * PathNode constructor.
     * @param $name
     * @param $latitude
     * @param $longitude
     */
    private function __construct($name, $latitude, $longitude)
    {
        $this->name = $name;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * @param $node Node
     * @return PathNode
     */
    public static function loadFromNode($node)
    {
        if($node->getData("station") != null) {
            /** @var $station GraphStation */
            $station = $node->getData("station");
            $pathNode = new PathNode($station->getName(),$station->getLatitude(),$station->getLongitude());
            return $pathNode;
        }
        else
        {
            $position = $node->getData("position");
            $pathNode = new PathNode($node->getTag(),$position[0],$position[1]);
            return $pathNode;
        }
    }

    public static function loadFromPath($nodes,$time = null)
    {
        if($time == null) $time = UtilFunctions::getCurrentTime();
        $pathNodes = [];
        for ($i = 0;$i < count($nodes);$i++) {
            $node = $nodes[$i];

            /** @var  $node Node
             *  @var $prev Node
             * @var $prevPNode PathNode
             */
            $pNode = self::loadFromNode($node);
            if(isset($prev) && isset($prevPNode)) {
                $edgeType = $prev->getEdgeTo($node)->getData("type");
                $prevPNode->setTransportModeToNextNode($edgeType);
                if($i < count($nodes)-1)
                if ($edgeType == "byFoot") {
                    $pNode->setWaitingTimeAtNode($node->getData("station")->getWaitingTime($time));
                } else {
                    $pNode->setWaitingTimeAtNode($node->getData("station")->getWaitingTimeAtTrip($time));
                }
            }
            $pathNodes[] = $pNode;
            $prev = $node;
            $prevPNode = $pNode;
        }
        return $pathNodes;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param mixed $latitude
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * @return mixed
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param mixed $longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * @return PathNode
     */
    public function getNext(): PathNode
    {
        return $this->next;
    }

    /**
     * @param PathNode $next
     */
    public function setNext(PathNode $next)
    {
        $this->next = $next;
    }

    /**
     * @return mixed
     */
    public function getWaitingTimeAtNode()
    {
        return $this->waitingTimeAtNode;
    }

    /**
     * @param mixed $waitingTimeAtNode
     */
    public function setWaitingTimeAtNode($waitingTimeAtNode)
    {
        $this->waitingTimeAtNode = $waitingTimeAtNode;
    }

    /**
     * @return mixed
     */
    public function getTransportModeToNextNode()
    {
        return $this->transportModeToNextNode;
    }

    /**
     * @param mixed $transportModeToNextNode
     */
    public function setTransportModeToNextNode($transportModeToNextNode)
    {
        $this->transportModeToNextNode = $transportModeToNextNode;
    }




}