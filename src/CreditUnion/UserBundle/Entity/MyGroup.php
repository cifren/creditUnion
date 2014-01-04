<?php

namespace CreditUnion\UserBundle\Entity;

use FOS\UserBundle\Entity\Group as BaseGroup;
use Doctrine\ORM\Mapping as ORM;

/**
 * CreditUnion\UserBundle\Entity\MyGroup
 * 
 * @ORM\Entity
 * @ORM\Table(name="fos_group")
 */
class MyGroup extends BaseGroup
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="MyUser", mappedBy="group")
     */
    protected $users;

}
