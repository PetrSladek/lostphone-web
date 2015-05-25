<?php
/**
 * Zpráva s registračními údaji nového zařízení.
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
class RegistrationMessage extends Message {

    /**
     * @return int
     */
    public function getType()
    {
        return Message::TYPE_REGISTRATION;
    }

    /**
     * Unikatní identifikátor zařízení
     * @Column(type="string")
     * @var string
     */
    protected $identifier;

    /**
     * Přidělené GCM ID
     * @Column(type="string")
     * @var string
     */
    protected $gcmId;

    /**
     * Google Account uživatele zařízení
     * @Column(type="string")
     * @var string
     */
    protected $googleAccountEmail;

    /**
     * Značka zařízení (např. Lenovo / Samsung / ...)
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $brand;

    /**
     * Model zařízení (např. S750 / Nexus 3 / ...)
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $model;


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
        $this->identifier = $identifier;
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
     * @return string
     */
    public function getGoogleAccountEmail()
    {
        return $this->googleAccountEmail;
    }

    /**
     * @param string $googleAccountEmail
     */
    public function setGoogleAccountEmail($googleAccountEmail)
    {
        $this->googleAccountEmail = $googleAccountEmail;
    }

    /**
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param string $brand
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
    }

    /**
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param string $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }



}