<?php
/**
 * Zpráva ze zařízení o nalezení zařízení.
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
class GotchaMessage extends Message {

    /**
     * @return int
     */
    public function getType()
    {
        return Message::TYPE_GOTCHA;
    }

}