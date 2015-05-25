<?php
/**
 * Zpráva ze zařízení s odpovědí na PING.
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
class PongMessage extends Message {

    /**
     * @return int
     */
    public function getType()
    {
        return Message::TYPE_PONG;
    }



}