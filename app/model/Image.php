<?php
/**
 * Created by PhpStorm.
 * User: Peggy
 * Date: 6.11.14
 * Time: 8:56
 */

namespace App\Model;


use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;

/**
 * @Entity
 */
class Image extends BaseEntity {

    use Identifier;

    /**
     * @Column(type="string")
     */
    protected $filename;

    /**
     * @Column(type="string")
     */
    protected $extension;

    /**
     * @return mixed
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param mixed $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return mixed
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param mixed $extension
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
    }






} 