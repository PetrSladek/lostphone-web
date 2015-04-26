<?php
/**
 * Created by PhpStorm.
 * User: Peggy
 * Date: 28.2.2015
 * Time: 11:50
 */

namespace App\Model\Commands;

use App\Model\Commands\Command;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

/**
 * Class LockCommand
 * @package App\Model
 * @Entity
 */
class LockCommand extends Command {

    /**
     * @return int
     */
    public function getType()
    {
        return Command::TYPE_LOCK;
    }

    /**
     * @Column(type="string")
     * @var string
     */
    protected $password;

    /**
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $ownerPhoneNumber;

    /**
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $displayText;

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getOwnerPhoneNumber()
    {
        return $this->ownerPhoneNumber;
    }

    /**
     * @param string $ownerPhoneNumber
     */
    public function setOwnerPhoneNumber($ownerPhoneNumber)
    {
        $this->ownerPhoneNumber = $ownerPhoneNumber;
    }

    /**
     * @return string
     */
    public function getDisplayText()
    {
        return $this->displayText;
    }

    /**
     * @param string $displayText
     */
    public function setDisplayText($displayText)
    {
        $this->displayText = $displayText;
    }





    /**
     * Data ktera se poslou pres GCM na zarizeni
     * @return array
     */
    public function toGCMdata()
    {
        $data = parent::toGCMdata(); // TODO: Change the autogenerated stub
        $data['password'] = $this->password;
        $data['ownerPhoneNumber'] = $this->ownerPhoneNumber;
        $data['displayText'] = $this->displayText;

        return $data;
    }


}