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
 * Class RegistrationMessage
 * @package App\Model
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
     * @Column(type="string")
     * @var string
     */
    protected $identifier;

    /**
     * @Column(type="string")
     * @var string
     */
    protected $gcmId;

    /**
     * @Column(type="string")
     * @var string
     */
    protected $googleAccountEmail;



    /**
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $brand;

    /**
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