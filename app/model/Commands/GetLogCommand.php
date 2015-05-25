<?php
/**
 * Příkaz do zařízení k získání výpisu SMS a volání.
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
class GetLogCommand extends Command {


    /**
     * @return int
     */
    public function getType()
    {
        return Command::TYPE_GETLOG;
    }


}