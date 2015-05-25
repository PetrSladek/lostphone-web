<?php
/**
 * Zpráva ze zařízení se aktuálním stavem SIM karty
 *
 * @package LostPhone
 * @author Petr Sládek <xslade12@stud.fit.vutbr.cz>
 */

namespace App\Model\Messages;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

/**
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
     * IMEI
     * International Mobile Equipment Identity. Jde o unikátní číslo přidělené výrobcem mobilnímu telefonu.
     *
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $imei;

    /**
     * IMSI
     * International Mobile Subscriber Identity. Jde o unikátní číslo přidělené mobilním operátorem pro SIM kartu v mobilní síti GSM nebo UMTS. Může být použito v dalších sítích jako např. CDMA.
     *
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $subscriberId;

    /**
     * MSISDN
     * Mobile Subscriber ISDN Number je celosvětově jednoznačné číslo, které identifikuje účastníka ve veřejné telefonní síti
     *
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $phoneNumber;

    /**
     * Operátor přihlášené sítě
     *
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $networkOperator;

    /**
     * Název operátora přihlášené sítě
     *
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $networkOperatorName;

    /**
     * ISO kód Země operátora přihlášené sítě
     *
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $networkCountryIso;

    /**
     * Operátor sítě vyddavajici SIM kartu
     *
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $simOperator;

    /**
     * Název operátora sítě vyddavajici SIM kartu
     *
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $simOperatorName;

    /**
     * Seriové číslo SIM karty
     *
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $simSerialNumber;

    /**
     * IISO kód Země operátora vydávající SIM kartu
     *
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $simCountryIso;




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