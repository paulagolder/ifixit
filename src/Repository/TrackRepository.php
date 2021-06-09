<?php

namespace App\Repository;

use App\Entity\Track;
use Doctrine\ORM\EntityRepository;


class TrackRepository extends EntityRepository
{

    public function findOne($fxid,$rpid)
    {
       $qb = $this->createQueryBuilder("p");
       $qb->where("p.FixerId = :fxid");
       $qb->setParameter('fxid', $fxid);
       $qb->andWhere("p.RepairId = :rpid");
       $qb->setParameter('rpid', $rpid);
       $track =  $qb->getQuery()->getOneOrNullResult();;
       return $track;
    }


    public function findAllTracks($fxid)
    {
       $qb = $this->createQueryBuilder("p");
       $qb->andWhere("p.FixerId = :fxid");
       $qb->setParameter('fxid', $fxid);
       $tracklist =  $qb->getQuery()->getResult();
       $tracks=[];
       foreach($tracklist as $track)
       {
       $tracks[$track->getRepairId()]=$track;
       }
       return $tracks;
    }

}
