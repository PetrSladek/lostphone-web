<?php
/**
 * Příkaz do zařízení k uzamčení.
 *
 * @package LostPhone
 * @author Petr Sládek <xslade12@stud.fit.vutbr.cz>
 */

namespace App\Model\Commands;

use App\Model\Commands\Command;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

/**
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
     * Heslo pro odemčení
     * @Column(type="string")
     * @var string
     */
    protected $password;

    /**
     * Telefoní číslo majitele, pro zavilání zpět
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $ownerPhoneNumber;

    /**
     * Text který se zobrazí na displeji zařízení
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
        $data = parent::toGCMdata();
        $data['password'] = $this->password;
        $data['ownerPhoneNumber'] = $this->ownerPhoneNumber;
        $data['displayText'] = $this->displayText;

        return $data;
    }


}