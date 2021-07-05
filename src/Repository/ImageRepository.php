<?php

namespace App\Repository;

use App\Entity\Image;
use Doctrine\ORM\EntityRepository;


class ImageRepository extends EntityRepository
{

    public function findAll()
    {
       $qb = $this->createQueryBuilder("p");
       $replies =  $qb->getQuery()->getResult();
       return $replies;
    }


    public function findOne($imgid)
    {
      $qb = $this->createQueryBuilder("p");
      $qb->where("p.ImageId = :imgid");
       $qb->setParameter('imgid', $imgid);
       $reply =  $qb->getQuery()->getOneOrNullResult();;
       return $reply;
    }

    public function findAllforRepair($rpid)
    {
      $qb = $this->createQueryBuilder("p");
      $qb->where("p.RepairId = :rpid");
       $qb->setParameter('rpid', $rpid);
       $images =  $qb->getQuery()->getResult();
          $image_array = [];
       foreach( $images as $image)
       {
         $image_array[$image->getStep()][] = $image;
       }
       return $image_array;
    }

}
