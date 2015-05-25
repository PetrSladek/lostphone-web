<?php
/**
 * Příkaz do zařízení ke zjištění polohy.
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
class LocateCommand extends Command {

    /**
     * @return int
     */
    public function getType()
    {
        return Command::TYPE_LOCATE;
    }




}