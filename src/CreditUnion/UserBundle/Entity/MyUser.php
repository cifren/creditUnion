<?php

namespace CreditUnion\UserBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * CreditUnion\UserBundle\Entity\MyUser
 * 
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class MyUser extends BaseUser
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="MyGroup", inversedBy="users")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", nullable=false)
     */
    protected $group;

    public function getGroup()
    {
        return $this->group;
    }

    public function setGroup($group)
    {
        $this->group = $group;
    }

    public function getRoles()
    {
        $roles = $this->roles;

        if ($this->group)
            $roles = array_merge($roles, $this->group->getRoles());

        // we need to make sure to have at least one role
        $roles[] = static::ROLE_DEFAULT;

        return array_unique($roles);
    }

}
