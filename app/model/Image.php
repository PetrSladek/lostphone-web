<?php
/**
 * Databázová Entita obrázku.
 *
 * @package LostPhone
 * @author Petr Sládek <xslade12@stud.fit.vutbr.cz>
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
     * Název souboru s obrázkem
     * @Column(type="string")
     */
    protected $filename;

    /**
     * Přípona
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