<?php
/**
 * Created by PhpStorm.
 * User: Peggy
 * Date: 28.2.2015
 * Time: 11:50
 */

namespace App\Model\Messages;


use App\Model\Commands\Command;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToOne;

/**
 * Class WrongPassMessage
 * @package App\Model
 * @Entity
 */
class WrongPassMessage extends Message {


    /**
     * Fotka
     *
     * @OneToOne(targetEntity="App\Model\Image")
     * @var Image
     */
    protected $frontPhoto;


    /**
     * @return int
     */
    public function getType()
    {
        return Message::TYPE_WRONGPASS;
    }







}