<?php

namespace App\Repository;

use App\Entity\Dialogreply;
use Doctrine\ORM\EntityRepository;


class DialogreplyRepository extends EntityRepository
{

    public function findAll()
    {
       $qb = $this->createQueryBuilder("p");
       $replies =  $qb->getQuery()->getResult();
       return $replies;
    }


     public function findOne($rpid,$dlgname)
    {
      $qb = $this->createQueryBuilder("p");
      $qb->where("p.RepairId = :rpid");
       $qb->setParameter('rpid', $rpid);
        $qb->andwhere("p.Dialogname = :dlgname");
       $qb->setParameter('dlgname', $dlgname);
       $reply =  $qb->getQuery()->getOneOrNullResult();;
       return $reply;
    }

}
