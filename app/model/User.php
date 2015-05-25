<?php
/**
 * Uživatel webové aplikace
 *
 * @package LostPhone
 * @author Petr Sládek <xslade12@stud.fit.vutbr.cz>
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
 * @Entity
 */
class User extends BaseEntity {

    use Identifier;

    /**
     * Jméno uživatle
     * @Column(type="string")
     * @var string
     */
    protected $name;

    /**
     * Google Account Email
     * @Column(type="string")
     * @var string
     */
    protected $googleEmail;

    /**
     * Uživatelovi zařízení
     * @OneToMany(targetEntity="Device", mappedBy="owner")
     * @var Collection
     */
    protected $devices;


    public function __construct()
    {
        $this->devices = new ArrayCollection();
    }


    /**
     * Přidat zařízení
     * @param Device $device
     */
    public function addDevice(Device $device) {
        $this->devices->add($device);
    }

    /**
     * Odebrat zařízení
     * @param Device $device
     */
    public function removeDevice(Device $device) {
        $this->devices->remove($device);
    }

    /**
     * Vrati prvni (defaultni zarizeni)
     * @return Device|null
     */
    public function getFirstDevice() {
        return $this->devices->count() ? $this->devices->first() : null;
    }

    /**
     * Vratí všechny zařízení uživatele
     * @return Collection
     */
    public function getDevices()
    {
        return $this->devices->toArray();
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
     * Převede uživatele na Nette Identitu pro přímé přihlášení
     * @return Identity
     */
    public function toIdentity() {
        return new Identity($this->getId());
    }





}