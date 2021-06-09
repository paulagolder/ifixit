<?php
// src/Controller/Main/MainController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Repair;
use App\Entity\Dialog;
use App\Entity\Dialogreply;
use App\Entity\Fixer;

class MainController extends AbstractController
{


  public function Overview()
  {
    $date = $objDateTime = new \DateTime('NOW');
    $repairs =  $this->getDoctrine()->getRepository("App:Repair")->findAll();
    foreach($repairs as $repair)
    {
      $rupdated = $repair->getupdated();
      $object =  $this->getDoctrine()->getRepository("App:Dialogreply")->findOne($repair->getRepairId(),"object");
      if($object->getUpdated() > $rupdated)
      {
        $fields= json_decode($object->getDialogreply());
        $repair->setName($fields->type." ".$fields->brand." ".$fields->model);
        $repair->setUpdated($date);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($repair);
        $entityManager->flush();
      }

    }
    dump($repairs);
    return $this->render('main/overview.html.twig', [
    'date' => $date,
    'repairs'=>$repairs,
    ]);;
  }


  public function fixerview($fxid)
  {
    $date = $objDateTime = new \DateTime('NOW');
    $repairs =  $this->getDoctrine()->getRepository("App:Repair")->findAll();
    $fixer = $this->getDoctrine()->getRepository("App:Fixer")->findOne($fxid);
    $tracks = $this->getDoctrine()->getRepository("App:Track")->findAllTracks($fxid);
    dump($repairs);
    dump($tracks);
    return $this->render('main/fixerview.html.twig', [
    'date' => $date,
    'repairs'=>$repairs,
    'tracks'=>$tracks,
    'fixer'=>$fixer,
    ]);;
  }

  public function fixers()
  {
    $date = $objDateTime = new \DateTime('NOW');
    $fixers = $this->getDoctrine()->getRepository("App:Fixer")->findAll();
    $tracks = $this->getDoctrine()->getRepository("App:Track")->findAll();
    dump($tracks);
    return $this->render('main/fixersview.html.twig', [
    'date' => $date,
    'tracks'=>$tracks,
    'fixers'=>$fixers,
    ]);;
  }

}
