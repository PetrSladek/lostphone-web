<?php
/**
 * Created by PhpStorm.
 * User: Peggy
 * Date: 3.3.2015
 * Time: 22:21
 */

namespace App\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Security\Identity;

/**
 * Class User
 * @package app\model
 *
 * @Entity
 */
class User extends BaseEntity {

    use Identifier;


    /**
     * @Column(type="string")
     * @var string
     */
    private $name;

    /**
     * @Column(type="string")
     * @var string
     */
    private $googleEmail;

    /**
     * @OneToMany(targetEntity="Device", mappedBy="owner")
     * @var Collection
     */
    private $devices;


    public function __construct()
    {
        $this->devices = new ArrayCollection();
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
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getGoogleEmail()
    {
        return $this->googleEmail;
    }

    /**
     * @param string $googleEmail
     */
    public function setGoogleEmail($googleEmail)
    {
        $this->googleEmail = $googleEmail;
    }


    /**
     * @return Identity
     */
    public function toIdentity() {
        return new Identity($this->getId());
    }





}