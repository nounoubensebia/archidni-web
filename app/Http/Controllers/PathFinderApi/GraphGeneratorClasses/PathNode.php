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
    private $idLine;
    private $idStation;
    private $idTrip;
    private $timeToNextNode;

    public function toArray()
    {
        $array = [];
        $array["name"] = $this->getName();
        $array["latitude"] = $this->getLatitude();
        $array["longitude"] = $this->getLongitude();
        $array["waitingTime"] = $this->getWaitingTimeAtNode();
        $array["transportModeToNextNode"] = $this->getTransportModeToNextNode();
        $array["idLine"] = $this->getIdLine();
        $array["idStation"] = $this->getIdStation();
        $array["idTrip"] = $this->getIdTrip();
        $array["timeToNextNode"] = $this->getTimeToNextNode();
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
            $pathNode->setIdLine($station->getTrip()->getLine()->id);
            $pathNode->setIdStation($station->getId());
            $pathNode->setIdTrip($station->getTrip()->getId());
            return $pathNode;
        }
        else
        {
            $position = $node->getData("position");
            $pathNode = new PathNode($node->getTag(),$position[0]+0,$position[1]+0);
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
                $edge = $prev->getEdgeTo($node);
                $edgeType = $edge->getData("type");
                $prevPNode->setTransportModeToNextNode($edgeType);
                $prevPNode->setTimeToNextNode($edge->getData("time"));
                if($i < count($nodes)-1)
                if ($edgeType == "byFoot") {
                    $walkTime = UtilFunctions::getTime($node->getData("position"),$prev->getData("position"));
                    $pNode->setWaitingTimeAtNode($node->getData("station")->getWaitingTime($time+$walkTime));

                } else {
                    $pNode->setWaitingTimeAtNode($node->getData("station")->getWaitingTimeAtTrip($time));
                }
                $time += $edge->getData("time"); // advance in time with time it takes to travel the edge
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
    public function getIdLine()
    {
        return $this->idLine;
    }

    /**
     * @param mixed $idLine
     */
    public function setIdLine($idLine)
    {
        $this->idLine = $idLine;
    }

    /**
     * @return mixed
     */
    public function getIdStation()
    {
        return $this->idStation;
    }

    /**
     * @param mixed $idStation
     */
    public function setIdStation($idStation)
    {
        $this->idStation = $idStation;
    }

    /**
     * @return mixed
     */
    public function getTimeToNextNode()
    {
        return $this->timeToNextNode;
    }

    /**
     * @param mixed $timeToNextNode
     */
    public function setTimeToNextNode($timeToNextNode)
    {
        $this->timeToNextNode = $timeToNextNode;
    }



    /**
     * @return mixed
     */
    public function getIdTrip()
    {
        return $this->idTrip;
    }

    /**
     * @param mixed $idTrip
     */
    public function setIdTrip($idTrip)
    {
        $this->idTrip = $idTrip;
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