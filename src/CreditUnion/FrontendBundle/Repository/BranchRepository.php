<?php

namespace CreditUnion\FrontendBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * CreditUnion\FrontendBundle\Repository\branchRepository
 * 
 */
class BranchRepository extends EntityRepository {

    public function getBranchesWithClient()
    {
        $branches = $this->createQueryBuilder('b')
                ->join('b.clients', 'c')
                ->where('c.id IS NOT NULL')
                ->orderBy('c.name', 'desc')
                ->getQuery()
                ->getResult();
        
        return $branches;
    }

}
