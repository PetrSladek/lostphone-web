<?php
/**
 * Created by PhpStorm.
 * User: Peggy
 * Date: 28.2.2015
 * Time: 11:50
 */

namespace App\Model\Commands;

use App\Model\Commands\Command;
use Doctrine\ORM\Mapping\Entity;

/**
 * Class WipeDataCommand
 * @package App\Model
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