<?php
/**
 * Created by PhpStorm.
 * User: Peggy
 * Date: 20.2.2015
 * Time: 15:21
 */

namespace App\Model\Commands;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use \DateTime;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @Entity
 * @InheritanceType("JOINED")
 * @DiscriminatorColumn(name="type", type="integer")
 * @DiscriminatorMap({
 *  Command::TYPE_PING = "PingCommand",
 *  Command::TYPE_RING = "RingCommand",
 *  Command::TYPE_LOCK = "LockCommand",
 *  Command::TYPE_LOCATE = "LocateCommand"
 * })
 */
abstract class Command extends BaseEntity {

    use \Kdyby\Doctrine\Entities\Attributes\Identifier; // Using Identifier trait for id col

    const TYPE_PING      = 0x0000;
    const TYPE_RING      = 0x0001;
    const TYPE_LOCK      = 0x0002;
    const TYPE_LOCATE    = 0x0003;


    /**
     * @Column(type="datetime", nullable=true)
     * @var DateTime|null Data sent
     */
    protected $dateSent;

    /**
     * @Column(type="datetime", nullable=true)
     * @var DateTime|null Date Acknowlage
     */
    protected $dateAck;



    /**
     * @return int
     */
    public abstract function getType();




    /**
     * @return DateTime|null
     */
    public function getDateSent()
    {
        return $this->dateSent;
    }

    /**
     * @param DateTime|null $dateSent
     */
    public function setDateSent($dateSent)
    {
        $this->dateSent = $dateSent;
    }

    /**
     * @return DateTime|null
     */
    public function getDateAck()
    {
        return $this->dateAck;
    }

    /**
     * @param DateTime|null $dateAck
     */
    public function setDateAck($dateAck)
    {
        $this->dateAck = $dateAck;
    }


    /**
     * @return bool
     */
    public function isAck() {
        return $this->dateAck !== null;
    }


    /**
     * Data ktera se poslou pres GCM na zarizeni
     * @return array
     */
    public function toGCMdata() {
        return array(
            'id' => $this->id,
            'type' => $this->getType(),
            'dateSent' => $this->dateSent,
        );
    }


}