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

/**
 * Class WrongPassMessage
 * @package App\Model
 * @Entity
 */
class WrongPassMessage extends Message {


    /**
     * Cesta k fotce
     * @Column(type="string", nullable=true)
     * @var string
     */
    private $frontPhoto;


    /**
     * @return int
     */
    public function getType()
    {
        return Message::TYPE_WRONGPASS;
    }


    /**
     * @return string
     */
    public function getFrontPhoto()
    {
        return $this->frontPhoto;
    }

    /**
     * @param string $frontPhoto
     */
    public function setFrontPhoto($frontPhoto)
    {
        $this->frontPhoto = $frontPhoto;
    }




}