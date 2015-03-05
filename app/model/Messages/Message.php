<?php
/**
 * Created by PhpStorm.
 * User: Peggy
 * Date: 3.3.2015
 * Time: 20:47
 */

namespace App\Model\Messages;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\InheritanceType;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @Entity()
 * @InheritanceType("JOINED")
 * @DiscriminatorColumn(name="type", type="integer")
 * @DiscriminatorMap({
 *  Message::TYPE_PONG = "PongMessage",
 *  Message::TYPE_REGISTRATION = "RegistrationMessage",
 *  Message::TYPE_GOTCHA = "GotchaMessage",
 *  Message::TYPE_RINGINGTIMEOUT = "RingingTimeoutMessage",
 *  Message::TYPE_UNLOCK = "UnlockMessage",
 *  Message::TYPE_WRONGPASS = "WrongPassMessage",
 *  Message::TYPE_LOCATION = "LocationMessage"
 * })
 */
abstract class Message extends BaseEntity {

    use \Kdyby\Doctrine\Entities\Attributes\Identifier; // Using Identifier trait for id col

    const TYPE_PONG              = 0x0000; // odpoved na PING, pouze testovaci
    const TYPE_REGISTRATION      = 0x0001;
    const TYPE_GOTCHA            = 0x0002;
    const TYPE_RINGINGTIMEOUT    = 0x0003;
    const TYPE_UNLOCK            = 0x0004;
    const TYPE_WRONGPASS         = 0x0005;
    const TYPE_LOCATION          = 0x0006;

    /**
     * @Column(type="datetime", nullable=true)
     * @var DateTime|null Data sent
     */
    private $dateSent;



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


}