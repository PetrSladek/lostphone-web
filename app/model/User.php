<?php
/**
 * Created by PhpStorm.
 * User: Peggy
 * Date: 3.3.2015
 * Time: 22:21
 */

namespace App\Model;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * Class User
 * @package app\model
 *
 * @Entity
 */
class User extends BaseEntity {

    use Identifier;


    /**
     * @Column(type="string")
     * @var string
     */
    private $name;

    /**
     * @Column(type="string")
     * @var string
     */
    private $googleEmail;

    // TODO devices

}