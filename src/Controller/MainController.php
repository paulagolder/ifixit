<?php
// src/Controller/Main/MainController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


use App\Entity\Repair;
use App\Entity\Dialog;
use App\Entity\Dialogreply;
use App\Entity\User;
use App\Service\MyLibrary;

class MainController extends AbstractController
{

  private $mylib;
  private $requestStack ;

  public function __construct( MyLibrary $mylib ,RequestStack $request_stack)
  {
    $this->mylib = $mylib;
    $this->requestStack = $request_stack;;
  }


  public function Overview()
  {
    $repairkey =null;
    $fixerkey=null;
    $fixer= null;

    if (isset($_COOKIE['repairkey']))
    {
      $repairkey = $_COOKIE['repairkey'];
    }
    if (isset($_COOKIE['fixerkey']))
    {
      $fixerkey = $_COOKIE['fixerkey'];
    }
    $date = new \DateTime('NOW');
    $fixer =  $this->getDoctrine()->getRepository("App:User")->findOneByKey($fixerkey);
    $repairs =  $this->getDoctrine()->getRepository("App:Repair")->findAll();
    foreach($repairs as $repair)
    {
      $rupdated = $repair->getupdated();
      $object =  $this->getDoctrine()->getRepository("App:Dialogreply")->findOne($repair->getRepairId(),"object");
      $client =  $this->getDoctrine()->getRepository("App:Dialogreply")->findOne($repair->getRepairId(),"client");
      if($object  && $object->getUpdated() > $rupdated)
      {
        $ofields= json_decode($object->getDialogreply());
        $cfields= json_decode($client->getDialogreply());
        $repair->setName($ofields->type." ".$ofields->brand." ".$ofields->model);
        $repair->setEmail($cfields->email);
        $repair->setUpdated($date);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($repair);
        $entityManager->flush();
      }
    }
    if($fixer)
    {
      $repairsfollowed =  $this->getDoctrine()->getRepository("App:Repair")->findCurrent($fixer->getId());
      $repairsother =  $this->getDoctrine()->getRepository("App:Repair")->findOthers($fixer->getId());
      dump($repairsfollowed);
      dump($repairsother);
    return $this->render('main/fixeroverview.html.twig', [
    'fixer'=>$fixer,
    'date'=> $date,
    'repairsfollowed' => $repairsfollowed,
    'repairsother'=>$repairsother,
    ]);;
    }
    else
    {
      return $this->render('main/useroverview.html.twig', [
      'repairkey'=>$repairkey,
      'date' => $date,
      'repairs'=>$repairs,
      ]);;
    }
  }


  public function fixerview($fxid)
  {
    $date = $objDateTime = new \DateTime('NOW');
    $repairs =  $this->getDoctrine()->getRepository("App:Repair")->findAll();
    $fixer = $this->getDoctrine()->getRepository("App:User")->findOne($fxid);
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
    $fixerkey =null;
    if (isset($_COOKIE['fixerkey']))
    {
      $fixerkey = $_COOKIE['repairkey'];
      $thefixer = $this->getDoctrine()->getRepository("App:User")->findByKey($fixerkey);
    }
    $date =  new \DateTime('NOW');
    $fixers = $this->getDoctrine()->getRepository("App:User")->findAll();
    $tracks = $this->getDoctrine()->getRepository("App:Track")->findAll();
    dump($tracks);
    return $this->render('main/fixersview.html.twig', [
    'date' => $date,
    'tracks'=>$tracks,
    'fixers'=>$fixers,
    ]);;
  }




}
