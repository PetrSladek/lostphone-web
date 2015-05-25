<?php
/**
 * Zpráva ze zařízení o tom, že někdo úspěšně odemknul zařízení.
 *
 * @package LostPhone
 * @author Petr Sládek <xslade12@stud.fit.vutbr.cz>
 */

namespace App\Model\Messages;


use App\Model\Commands\Command;
use Doctrine\ORM\Mapping\Entity;

/**
 * @Entity
 */
class UnlockMessage extends Message {

    /**
     * @return int
     */
    public function getType()
    {
        return Message::TYPE_UNLOCK;
    }
    
}