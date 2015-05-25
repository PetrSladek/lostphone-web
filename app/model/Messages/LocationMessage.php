<?php
/**
 * Zpráva ze zařízení o zjištění polohy.
 *
 * @package LostPhone
 * @author Petr Sládek <xslade12@stud.fit.vutbr.cz>
 */


namespace App\Model\Messages;


use App\Model\Commands\Command;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

/**
 * @Entity
 */
class LocationMessage extends Message {

    /**
     * Zeměpisná šířka
     * @Column(type="decimal", precision=8, scale=6)
     * @var float
     */
    protected $lat;

    /**
     * Zeměpisná délka
     * @Column(type="decimal", precision=8, scale=6)
     * @var float
     */
    protected $lng;



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