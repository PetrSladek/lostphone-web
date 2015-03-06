<?php
/**
 * Created by PhpStorm.
 * User: Peggy
 * Date: 3.3.2015
 * Time: 22:21
 */

namespace App\Model;

use App\Model\Messages\RegistrationMessage;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
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

    use Identifier;

    /**
     * @Column(type="string")
     * @var string
     */
    private $identifier;

    /**
     * @Column(type="string")
     * @var string
     */
    private $name;


    /**
     * @ManyToOne(targetEntity="User", inversedBy="commentsAuthored")
     * @var User
     */
    private $owner;


    /**
     * @OneToOne(targetEntity="App\Model\Messages\RegistrationMessage")
     * @var RegistrationMessage
     */
    private $registrationMessage;

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




}