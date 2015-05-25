<?php
/**
 * Příkaz do zařízení k zašifrování dat.
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
class EncryptStorageCommand extends Command {

    /**
     * @return int
     */
    public function getType()
    {
        return Command::TYPE_ENCRYPTSTORAGE;
    }


}