<?php
/**
 * Příkaz do zařízení k ověření spojení.
 *
 * @package LostPhone
 * @author Petr Sládek <xslade12@stud.fit.vutbr.cz>
 */

namespace App\Model\Commands;

use App\Model\Commands\Command;
use Doctrine\ORM\Mapping\Entity;

/**
 * @Entity
 */
class PingCommand extends Command {

    /**
     * @return int
     */
    public function getType()
    {
        return Command::TYPE_PING;
    }


}