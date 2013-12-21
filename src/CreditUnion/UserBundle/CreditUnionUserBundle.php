<?php
namespace CreditUnion\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class CreditUnionUserBundle extends Bundle
{

    public function getParent()
    {
        return 'FOSUserBundle';
    }

}
