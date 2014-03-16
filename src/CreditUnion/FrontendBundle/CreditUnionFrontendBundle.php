<?php

namespace CreditUnion\FrontendBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class CreditUnionFrontendBundle extends Bundle
{
    public function boot()
    {
        \Doctrine\DBAL\Types\Type::overrideType('datetime', 'Doctrine\DBAL\Types\VarDateTime');
    }
}
