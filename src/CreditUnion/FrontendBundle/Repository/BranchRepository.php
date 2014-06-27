<?php

namespace CreditUnion\FrontendBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * CreditUnion\FrontendBundle\Repository\fininstitutRepository
 * 
 */
class FininstitutRepository extends EntityRepository {

    public function getFininstitutesWithClient()
    {
        $fininstitutes = $this->createQueryBuilder('b')
                ->join('b.clients', 'c')
                ->where('c.id IS NOT NULL')
                ->orderBy('c.name', 'desc')
                ->getQuery()
                ->getResult();
        
        return $fininstitutes;
    }

}
