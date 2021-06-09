<?php

namespace App\Repository;

use App\Entity\Fixer;
use Doctrine\ORM\EntityRepository;


class FixerRepository extends EntityRepository
{

    public function findOne($fid)
    {
       $qb = $this->createQueryBuilder("p");
       $qb->where("p.FixerId = :fid");
       $qb->setParameter('fid', $fid);
       $fixer =  $qb->getQuery()->getOneOrNullResult();;
       return $fixer;
    }


    public function findAll()
    {
       $qb = $this->createQueryBuilder("p");
       $fixers =  $qb->getQuery()->getResult();
       return $fixers;
    }

}
