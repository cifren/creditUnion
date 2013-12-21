<?php

namespace CreditUnion\UserBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * CreditUnion\UserBundle\Entity\User
 * 
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class MyUser extends BaseUser
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    public function getRolesImplode(){
        if(!empty($this->roles)){
            return implode(', ', $this->roles);
        }else { 
            return '';
        }
    }
}
