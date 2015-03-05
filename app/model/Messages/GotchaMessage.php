<?php
/**
 * Created by PhpStorm.
 * User: Peggy
 * Date: 28.2.2015
 * Time: 11:50
 */

namespace App\Model\Messages;


use App\Model\Commands\Command;
use Doctrine\ORM\Mapping\Entity;

/**
 * Class GotchaMessage
 * @package App\Model
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