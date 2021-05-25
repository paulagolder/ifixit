<?php

namespace App\Repository;

use App\Entity\Dialog;
use Doctrine\ORM\EntityRepository;


class DialogRepository extends EntityRepository
{

    public function findAll()
    {
       $qb = $this->createQueryBuilder("p");
       $dialogs =  $qb->getQuery()->getResult();
       return $dialogs;
    }

    public function findOnebyName($dname)
    {
      $qb = $this->createQueryBuilder("p");
        $qb->andwhere("p.Dname = :dname");
       $qb->setParameter('dname', $dname);
       $reply =  $qb->getQuery()->getOneOrNullResult();;
       return $reply;
    }

    public function findOnebyId($dlgid)
    {
      $qb = $this->createQueryBuilder("p");
        $qb->andwhere("p.DialogId = :dlgid");
       $qb->setParameter('dlgid', $dlgid);
       $reply =  $qb->getQuery()->getOneOrNullResult();;
       return $reply;
    }

}
