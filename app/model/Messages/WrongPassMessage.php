<?php
/**
 * Zpráva ze zařízení o tom, že se někdo pokusil odemknout zařízení.
 *
 * @package LostPhone
 * @author Petr Sládek <xslade12@stud.fit.vutbr.cz>
 */

namespace App\Model\Messages;


use App\Model\Commands\Command;
use App\Model\Image;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToOne;

/**
 * @Entity
 */
class WrongPassMessage extends Message {


    /**
     * Fotka z přední kamery
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

    /**
     * @return Image
     */
    public function getFrontPhoto()
    {
        return $this->frontPhoto;
    }

    /**
     * @param Image $frontPhoto
     */
    public function setFrontPhoto($frontPhoto)
    {
        $this->frontPhoto = $frontPhoto;
    }










}