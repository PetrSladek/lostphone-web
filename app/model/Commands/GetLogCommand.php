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
 * Class RingCommand
 * @package App\Model
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