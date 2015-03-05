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
    protected $imei;

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
     * @return string
     */
    public function getImei()
    {
        return $this->imei;
    }

    /**
     * @param string $imei
     */
    public function setImei($imei)
    {
        $this->imei = $imei;
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





}