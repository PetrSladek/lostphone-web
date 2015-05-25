<?php
/**
 * Příkaz do zařízení k uvedení do továrního nastevení.
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
class WipeDataCommand extends Command {

    /**
     * @return int
     */
    public function getType()
    {
        return Command::TYPE_WIPEDATA;
    }


}