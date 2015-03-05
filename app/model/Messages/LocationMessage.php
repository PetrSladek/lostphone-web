<?php
/**
 * Created by PhpStorm.
 * User: Peggy
 * Date: 28.2.2015
 * Time: 11:50
 */

namespace App\Model\Messages;


use App\Model\Commands\Command;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

/**
 * Class LocationMessage
 * @package App\Model
 * @Entity
 */
class LocationMessage extends Message {

    /**
     * @Column(type="decimal", precision=8, scale=6)
     * @var float
     */
    private $lat;

    /**
     * @Column(type="decimal", precision=8, scale=6)
     * @var float
     */
    private $lng;



    /**
     * @return int
     */
    public function getType()
    {
        return Message::TYPE_LOCATION;
    }

    /**
     * @return float
     */
    public function getLat()
    {
        return (float) $this->lat;
    }

    /**
     * @param float $lat
     */
    public function setLat($lat)
    {
        $this->lat = (float) $lat;
    }

    /**
     * @return float
     */
    public function getLng()
    {
        return (float) $this->lng;
    }

    /**
     * @param float $lng
     */
    public function setLng($lng)
    {
        $this->lng = (float) $lng;
    }






}