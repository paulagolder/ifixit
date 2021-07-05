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
       $qb = $this->createQueryBuilder('r', 'r.RepairId');
       $repairs =  $qb->getQuery()->getResult();
       return $repairs;
    }

    public function findCurrent($fxid)
    {
         $qb =  $this->createQueryBuilder('r', 'r.RepairId');
         $qb->select('r');
         $qb->join('App:Track', 't', 'WITH', 't.RepairId = r.RepairId');
         $qb ->where('t.FixerId = :fxid');
         $qb->setParameter('fxid', $fxid);
         $currentrepairs =  $qb->getQuery()->getResult();
         return $currentrepairs;
    }

    public function findOthers($fxid)
    {
         $all = $this->findAll();
         $some = $this->findCurrent($fxid);
         $rest=array_diff_key($all, $some);
         return $rest;
    }


    public function isUniqueName($name)
    {
       $qb = $this->createQueryBuilder("p");
       $qb->where("p.Name = :name");
       $qb->setParameter('name', $name);
       $fixer =  $qb->getQuery()->getOneOrNullResult();
       if(is_null($fixer) ) return true;
       else return false;
    }


    public function findByName($name)
    {
       $qb = $this->createQueryBuilder("p");
       $qb->where("p.Name = :name");
       $qb->setParameter('name', $name);
       $repair =  $qb->getQuery()->getOneOrNullResult();
        return $repair;
    }

    public function existName($name)
    {
       $qb = $this->createQueryBuilder("p");
       $qb->where("p.Name = :name");
       $qb->setParameter('name', $name);
       $repair =  $qb->getQuery()->getOneOrNullResult();
       if(is_null($repair) ) return false;
       else return false;
    }


    public function setKeys($email, $key)
    {
      $conn = $this->getEntityManager()->getConnection();
      $sql = " update repair set repairkey = '$key' where email= '$email' ";
      dump($sql);
      $conn->executeUpdate($sql);
    }


}
