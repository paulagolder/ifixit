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

    public function findAllforRepair($rpid)
    {
      $qb = $this->createQueryBuilder("p");
      $qb->where("p.RepairId = :rpid");
       $qb->setParameter('rpid', $rpid);
       $replies =  $qb->getQuery()->getResult();
          $replies_array = [];
       foreach( $replies as $reply)
       {
         $replies_array[$reply->getDialogname()] = $reply;
       }
       return $replies_array;
    }

}
