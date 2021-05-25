<?php

namespace App\Repository;

use App\Entity\Repair;
use Doctrine\ORM\EntityRepository;


class RepairRepository extends EntityRepository
{

    public function findOne($rpid)
    {
       $qb = $this->createQueryBuilder("p");
       $qb->where("p.RepairId = :rpid");
       $qb->setParameter('rpid', $rpid);
       $repair =  $qb->getQuery()->getOneOrNullResult();;
       return $repair;
    }


    public function findAll()
    {
       $qb = $this->createQueryBuilder("p");
       $repairs =  $qb->getQuery()->getResult();
       return $repairs;
    }

}
