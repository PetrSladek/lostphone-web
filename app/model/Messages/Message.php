<?php
/**
 * Abstraktní zpráva ze zařízení (předek ostatních zpráv)
 *
 * @package LostPhone
 * @author Petr Sládek <xslade12@stud.fit.vutbr.cz>
 */

namespace App\Model\Messages;
use App\Model\Device;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\ManyToOne;
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
 *  Message::TYPE_LOCATION = "LocationMessage",
 *  Message::TYPE_SIMSTATECHANGED = "SimStateChangedMessage",
 *  Message::TYPE_LOG = "LogMessage"
 * })
 */
abstract class Message extends BaseEntity {

    use \Kdyby\Doctrine\Entities\Attributes\Identifier; // Identifikační etita (ma ID)

    const TYPE_PONG              = 0x0000; // odpoved na PING, pouze testovaci
    const TYPE_REGISTRATION      = 0x0001; // registrace noveho zařízení
    const TYPE_GOTCHA            = 0x0002; // Mám tě
    const TYPE_RINGINGTIMEOUT    = 0x0003; // Prozvánení vypršelo
    const TYPE_UNLOCK            = 0x0004; // Zařízení odemknuto
    const TYPE_WRONGPASS         = 0x0005; // Pokus o odemčení
    const TYPE_LOCATION          = 0x0006; // Poloha zařízení
    const TYPE_SIMSTATECHANGED   = 0x0007; // Změna stavu SIM karty
    const TYPE_LOG               = 0x0008; // Výpisy volnání a SMS


    /**
     * Datum odeslání zprávy
     * @Column(type="datetime", nullable=true)
     * @var DateTime|null
     */
    protected $dateSent;

    /**
     * Zařízení ze kterého byla odeslána
     * @ManyToOne(targetEntity="App\Model\Device", inversedBy="messages")
     * @var Device
     */
    protected $device;

    /**
     * @return int
     */
    public abstract function getType();


    /**
     * Je zpráva zadaného typu?
     * @param int $type
     * @return bool
     */
    public function isType($type) {
        return $this->getType() === $type;
    }




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
     * @return Device
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * @param Device $device
     */
    public function setDevice(Device $device)
    {
        $this->device = $device;
    }







}