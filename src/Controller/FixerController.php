<?php
// src/Controller/Main/MainController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Reply;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Form\Type\FixerForm;
use App\Entity\Repair;
use App\Entity\Track;
use App\Entity\Fixer;
use App\Entity\Dialogreply;

class FixerController extends AbstractController
{
  private $requestStack;
  private $defaultscript;

  public function __construct( RequestStack $request_stack)
  {
    $this->requestStack = $request_stack;
    $this->defaultscript = "client,object,powered,working,fault,end";
  }




  public function view($fxid,$rpid)
  {
    $dialogs = $this->getDoctrine()->getRepository("App:Dialog")->findAll();
    $request = $this->requestStack->getCurrentRequest();
    if($rpid)
    {
      $arepair = $this->getDoctrine()->getRepository("App:Repair")->findOne($rpid);
      $script[] = explode(",",$arepair->getScript());
      $replies = $this->getDoctrine()->getRepository("App:Dialogreply")->findAllforRepair($rpid);
    }


    return $this->render('fixer/view.html.twig', array(
      "dialogs"=>$dialogs,
      'rpid'=>$rpid,
      'script'=>$script[0],
      'repair'=>$arepair,
      'replies'=>$replies,
      'returnlink'=>'/',
      ));
  }

  public function follow($fxid,$rpid)
  {
    $date = new \DateTime();


    $atrack= $this->getDoctrine()->getRepository("App:Track")->findOne($fxid,$rpid);
    if(! isset($atrack))
    {
      $atrack= new track();
      $atrack->setRepairId($rpid);
      $atrack->setFixerId($fxid);
      $atrack->setFollow(false);
    }
      $atrack->setUpdated($date);
      $atrack->setFollow(!$atrack->getFollow() );
      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->persist($atrack);
      $entityManager->flush();
      $trkid = $atrack->getTrackId();


    return $this->redirectToRoute('fixer', ['fxid' => $fxid,]);
  }


  public function update($rpid, $step)
  {
    $dialogs = $this->getDoctrine()->getRepository("App:Dialog")->findAll();
    $arepair = $this->getDoctrine()->getRepository("App:Repair")->findOne($rpid);
    $dialog =   $this->getDoctrine()->getRepository("App:Dialog")->findOnebyName($step);
    $areply = $this->getDoctrine()->getRepository("App:Dialogreply")->findOne($rpid,$dialog->getDname());
    if(!$areply)
    {
      $areply = new Dialogreply();
      $areply->setRepairId($rpid);
      $areply->setDialogname($dialog->getDname());
      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->persist($areply);
      $entityManager->flush();
      $replyid = $areply->getReplyId();
    }
    $request = $this->requestStack->getCurrentRequest();
    dump($areply->getDialogreply());
    if($request->getMethod() == 'POST')
    {
      $date = new \DateTime();
      $fields= $dialog->getDfields();
      $data=[];
      foreach($fields as $field)
      {
        $data[$field["fname"]] = $request->get($field["fname"]);
      }
      dump($data);
      $areply->setDialogreply(json_encode($data));
      $areply->setUpdated($date);
      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->persist($areply);
      $entityManager->flush();
    }
    if($dialog->getDnext() && $dialog->getDnext()!= "")
    {
      $step = $dialog->getDnext();
      dump($step);
    }
    return $this->redirectToRoute('repair_edit', ['rpid' => $rpid,'step'=>$step,]);
  }

}
