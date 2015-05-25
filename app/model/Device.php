<?php
/**
 * Entita definující zařízení
 *
 * @package LostPhone
 * @author Petr Sládek <xslade12@stud.fit.vutbr.cz>
 */

namespace App\Model;

use App\Model\Commands\Command;
use App\Model\Messages\Message;
use App\Model\Messages\RegistrationMessage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette;

/**
 * Class Device
 * @package app\model
 *
 * @Entity
 */
class Device extends BaseEntity {

    use Identifier; // Entita s ID

    /**
     * Unikatní idetifikátor zaříázení
     * @Column(type="string")
     * @var string
     */
    protected $identifier;

    /**
     * Přidelení GCM id
     * @Column(type="string")
     * @var string
     */
    protected $gcmId;

    /**
     * Název zařízení
     * @Column(type="string")
     * @var string
     */
    protected $name;

    /**
     * Je zařízení aktuálně uzamknuté?
     * @Column(type="boolean")
     * @var boolean
     */
    protected $locked = false;


    /**
     * Uživatel kterému toto zařízení patří
     * @ManyToOne(targetEntity="User", inversedBy="devices")
     * @var User
     */
    protected $owner;

    /**
     * Registrační zpráva, kterou bylo zařízení přidáno
     * @OneToOne(targetEntity="App\Model\Messages\RegistrationMessage")
     * @var RegistrationMessage
     */
    protected $registrationMessage;

    /**
     * Příkazy, které byli na toto zařízení odeslány
     * @OneToMany(targetEntity="App\Model\Commands\Command", mappedBy="device")
     * @var Collection
     */
    protected $commands;


    /**
     * Zprávy které z tohoto zařízení odešli
     * @OneToMany(targetEntity="App\Model\Messages\Message", mappedBy="device")
     * @var Collection
     */
    protected $messages;


    public function __construct()
    {
        // Aby při založení entity přes konstructor byli inicializovány prázdné kolekce
        $this->messages = new ArrayCollection();
        $this->commands = new ArrayCollection();
    }


    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = trim($identifier);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = trim($name);
    }



    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param User $owner
     */
    public function setOwner(User $owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return string
     */
    public function getGcmId()
    {
        return $this->gcmId;
    }

    /**
     * @param string $gcmId
     */
    public function setGcmId($gcmId)
    {
        $this->gcmId = $gcmId;
    }

    /**
     * @return boolean
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * @param boolean $locked
     */
    public function setLocked($locked = true)
    {
        $this->locked = $locked;
    }






    /**
     * @return RegistrationMessage
     */
    public function getRegistrationMessage()
    {
        return $this->registrationMessage;
    }

    /**
     * @param RegistrationMessage $registrationMessage
     */
    public function setRegistrationMessage($registrationMessage)
    {
        $this->registrationMessage = $registrationMessage;
    }

    /**
     * @param Message $command
     * @return $this
     */
    public function addCommand(Command $command)
    {
        $this->commands->add($command);
        $command->setDevice($this);
        return $this;
    }

    /**
     * @param Message $messages
     * @return $this
     */
    public function removeCommand(Command $command)
    {
        $this->commands->removeElement($command);
        $command->setDevice(null);
        return $this;
    }

    /**
     * @param Message $message
     * @return $this
     */
    public function addMessage(Message $message)
    {
        $this->messages->add($message);
        $message->setDevice($this);
        return $this;
    }

    /**
     * @param Message $messages
     * @return $this
     */
    public function removeMessage(Message $message)
    {
        $this->messages->removeElement($message);
        $message->setDevice(null);
        return $this;
    }







}