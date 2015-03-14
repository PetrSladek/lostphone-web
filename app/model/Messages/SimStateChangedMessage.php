<?php
/**
 * Created by PhpStorm.
 * User: Peggy
 * Date: 28.2.2015
 * Time: 11:50
 */

namespace App\Model\Messages;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

/**
 * Class RegistrationMessage
 * @package App\Model
 * @Entity
 */
class SimStateChangedMessage extends Message {

    /**
     * @return int
     */
    public function getType()
    {
        return Message::TYPE_SIMSTATECHANGED;
    }

    /**
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $imei;

    /**
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $subscriberId;

    /**
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $phoneNumber;

    /**
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $networkOperator;

    /**
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $networkOperatorName;

    /**
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $networkCountryIso;

    /**
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $simOperator;

    /**
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $simOperatorName;

    /**
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $simCountryIso;

    /**
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $simSerialNumber;



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
    public function getSubscriberId()
    {
        return $this->subscriberId;
    }

    /**
     * @param string $subscriberId
     */
    public function setSubscriberId($subscriberId)
    {
        $this->subscriberId = $subscriberId;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return string
     */
    public function getNetworkOperator()
    {
        return $this->networkOperator;
    }

    /**
     * @param string $networkOperator
     */
    public function setNetworkOperator($networkOperator)
    {
        $this->networkOperator = $networkOperator;
    }

    /**
     * @return string
     */
    public function getNetworkOperatorName()
    {
        return $this->networkOperatorName;
    }

    /**
     * @param string $networkOperatorName
     */
    public function setNetworkOperatorName($networkOperatorName)
    {
        $this->networkOperatorName = $networkOperatorName;
    }

    /**
     * @return string
     */
    public function getNetworkCountryIso()
    {
        return $this->networkCountryIso;
    }

    /**
     * @param string $networkCountryIso
     */
    public function setNetworkCountryIso($networkCountryIso)
    {
        $this->networkCountryIso = $networkCountryIso;
    }

    /**
     * @return string
     */
    public function getSimOperator()
    {
        return $this->simOperator;
    }

    /**
     * @param string $simOperator
     */
    public function setSimOperator($simOperator)
    {
        $this->simOperator = $simOperator;
    }

    /**
     * @return string
     */
    public function getSimOperatorName()
    {
        return $this->simOperatorName;
    }

    /**
     * @param string $simOperatorName
     */
    public function setSimOperatorName($simOperatorName)
    {
        $this->simOperatorName = $simOperatorName;
    }

    /**
     * @return string
     */
    public function getSimCountryIso()
    {
        return $this->simCountryIso;
    }

    /**
     * @param string $simCountryIso
     */
    public function setSimCountryIso($simCountryIso)
    {
        $this->simCountryIso = $simCountryIso;
    }

    /**
     * @return string
     */
    public function getSimSerialNumber()
    {
        return $this->simSerialNumber;
    }

    /**
     * @param string $simSerialNumber
     */
    public function setSimSerialNumber($simSerialNumber)
    {
        $this->simSerialNumber = $simSerialNumber;
    }





}