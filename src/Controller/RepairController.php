<?php
// src/Controller/Main/MainController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Reply;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;


use Symfony\Component\Routing\Annotation\Route;


use App\Form\Type\RepairForm;
use App\Entity\Repair;
use App\Entity\Image;
use App\Entity\Dialog;
use App\Entity\Dialogreply;
use App\Service\FileUploader;

class RepairController extends AbstractController
{
  private $requestStack;
  private $defaultscript;

  public function __construct( RequestStack $request_stack)
  {
    $this->requestStack = $request_stack;
    $this->defaultscript = "client,object,powered,working,fault,end";
  }



  public function new()
  {
    $dialogs = $this->getDoctrine()->getRepository("App:Dialog")->findAll();
    $request = $this->requestStack->getCurrentRequest();

    $step="client";
    $arepair= new repair();
    $arepair->setName("new repair add name");
    $date = new \DateTime();
    $arepair->setUpdated($date);
    $script[] = explode(",",$this->defaultscript);
    $arepair->setScript($this->defaultscript);
    $entityManager = $this->getDoctrine()->getManager();
    $entityManager->persist($arepair);
    $entityManager->flush();
    $rpid = $arepair->getRepairid();
    $arepair->setName("Repair ($rpid)" );
    $entityManager->persist($arepair);
    $entityManager->flush();
    $replies = null;



    return $this->render('repair/script.html.twig', array(
      "dialogs"=>$dialogs,
      "step" => $step,
      'rpid'=>$rpid,
      'script'=>$script[0],
      'repair'=>$arepair,
      'replies'=>$replies,
      'returnlink'=>'/',
      ));

  }



  public function edit($rpid,$step)
  {

    $dialogs = $this->getDoctrine()->getRepository("App:Dialog")->findAll();
    $request = $this->requestStack->getCurrentRequest();
    if($rpid>0)
    {
      $arepair = $this->getDoctrine()->getRepository("App:Repair")->findOne($rpid);
      dump($arepair->getEmail());
      dump(strlen($arepair->getEmail()));
      if(strlen($arepair->getEmail())>5)
      {
        if (!isset($_COOKIE['repairkey'.$rpid]))
        {
          return $this->connect($rpid);
        }
        $repairkey = $_COOKIE['repairkey'.$rpid];
        $arepair = $this->getDoctrine()->getRepository("App:Repair")->findOne($rpid);
        if(!$repairkey || $repairkey != $arepair->getRepairkey())
        {
          return $this->connect($rpid);
        }
        $script[] = explode(",",$arepair->getScript());
        $replies = $this->getDoctrine()->getRepository("App:Dialogreply")->findAllforRepair($rpid);
      }
      else // no email address
      {
        $script[] = explode(",",$arepair->getScript());
        $replies = $this->getDoctrine()->getRepository("App:Dialogreply")->findAllforRepair($rpid);
      }
      $images = $this->getDoctrine()->getRepository("App:Image")->findAllforRepair($rpid);
    }
    else
    {
      return $this->redirectToRoute('index');
    }

    if(! isset($arepair))
    {
      $step="client";
      $arepair= new repair();
      $arepair->setName("new repair add name");
      $date = new \DateTime();
      $arepair->setUpdated($date);
      $script[] = explode(",",$this->defaultscript);
      $arepair->setScript($this->defaultscript);
      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->persist($arepair);
      $entityManager->flush();
      $rpid = $arepair->getRepairid();
      $replies = null;

    }

    return $this->render('repair/script.html.twig', array(
      "dialogs"=>$dialogs,
      "step" => $step,
      'rpid'=>$rpid,
      'script'=>$script[0],
      'repair'=>$arepair,
      'replies'=>$replies,
      'images'=>$images,
      'returnlink'=>'/',
      ));
  }

  public function addquestion($rpid,$step)
  {

    $dialogs = $this->getDoctrine()->getRepository("App:Dialog")->findAll();

      $arepair = $this->getDoctrine()->getRepository("App:Repair")->findOne($rpid);
      dump($arepair->getEmail());
      dump(strlen($arepair->getEmail()));
      if(strlen($arepair->getEmail())>5)
      {
        if (!isset($_COOKIE['repairkey'.$rpid]))
        {
          return $this->connect($rpid);
        }
        $repairkey = $_COOKIE['repairkey'.$rpid];
        $arepair = $this->getDoctrine()->getRepository("App:Repair")->findOne($rpid);
        if(!$repairkey || $repairkey != $arepair->getRepairkey())
        {
          return $this->connect($rpid);
        }
        $script[] = explode(",",$arepair->getScript());
        $replies = $this->getDoctrine()->getRepository("App:Dialogreply")->findAllforRepair($rpid);
      }
      else // no email address
      {
        $script[] = explode(",",$arepair->getScript());
        $replies = $this->getDoctrine()->getRepository("App:Dialogreply")->findAllforRepair($rpid);
      }
      $images = $this->getDoctrine()->getRepository("App:Image")->findAllforRepair($rpid);




    return $this->render('repair/fixerview.html.twig', array(
      "dialogs"=>$dialogs,
      "step" => $step,
      'rpid'=>$rpid,
      'script'=>$script[0],
      'repair'=>$arepair,
      'replies'=>$replies,
      'images'=>$images,
      'returnlink'=>'/',
      ));
  }


  public function connect($rpid)
  {
    $arepair = $this->getDoctrine()->getRepository("App:Repair")->findOne($rpid);
    return $this->render('repair/connect.html.twig', array('arepair'=>$arepair,'returnlink'=>'/', ));
  }

  public function confirmemail($rpid, MailerInterface $mailer)
  {
    $key = $_GET["key"];
    $arepair = $this->getDoctrine()->getRepository("App:Repair")->findOne($rpid);
    if(!($arepair->getRepairkey()== $key))
    {
      return $this->redirectToRoute('/');
    }
    $repairkey =  date('YmdHis');
    $cookie = new Cookie
    (
      'repairkey'.$rpid,	// Cookie name.
      $repairkey,	// Cookie value.
      time() + ( 1 * 30 * 24 * 60 * 60)	// Expires 1 moth.
      );
      $res = new Response();
      $res->headers->setCookie( $cookie );
      $res->send();
      $email=$arepair->getEmail();
      $this->getDoctrine()->getRepository("App:Repair")->setKeys( $email,$repairkey);
      return $this->redirectToRoute('repair_show', ['rpid' => $rpid,]);
  }


  public function sendemail($rpid, MailerInterface $mailer, MessageController  $messenger)
  {
    $arepair = $this->getDoctrine()->getRepository("App:Repair")->findOne($rpid);
    $this->addFlash('flash-message'," Reply to a new email to confirm changed/new email address ");
    $arepair->setRepairkey(mt_rand(100000, 999999));
    $arepair->setEmail($arepair->getEmail());
    $date = new \DateTime();
    $arepair->setUpdated($date);
    $entityManager = $this->getDoctrine()->getManager();
    $entityManager->persist($arepair);
    $entityManager->flush();
    $messenger->sendRepairEmail($arepair );
    return $this->redirectToRoute('index');
  }


  public function view($rpid)
  {
    $dialogs = $this->getDoctrine()->getRepository("App:Dialog")->findAll();
    $request = $this->requestStack->getCurrentRequest();
    $access = 'PUBLIC';


      $arepair = $this->getDoctrine()->getRepository("App:Repair")->findOne($rpid);
      if (isset($_COOKIE['repairkey'.$rpid]))
      {
        $repairkey = $_COOKIE['repairkey'.$rpid];
        if(!$repairkey || $repairkey != $arepair->getRepairkey())  $access = 'PUBLIC';
      }
      $script[] = explode(",",$arepair->getScript());
      $replies = $this->getDoctrine()->getRepository("App:Dialogreply")->findAllforRepair($rpid);



    return $this->render('repair/show.html.twig', array(
      'access'=>$access,
      "dialogs"=>$dialogs,
      'rpid'=>$rpid,
      'script'=>$script[0],
      'repair'=>$arepair,
      'replies'=>$replies,
      'returnlink'=>'/',
      ));
  }

  public function fixerview($fxid,$rpid)
  {
    $dialogs = $this->getDoctrine()->getRepository("App:Dialog")->findAll();
    $request = $this->requestStack->getCurrentRequest();
    $access = 'PUBLIC';


    $arepair = $this->getDoctrine()->getRepository("App:Repair")->findOne($rpid);
    if (isset($_COOKIE['repairkey'.$rpid]))
    {
      $repairkey = $_COOKIE['repairkey'.$rpid];
      if(!$repairkey || $repairkey != $arepair->getRepairkey())  $access = 'PUBLIC';
    }
    $script[] = explode(",",$arepair->getScript());
    $replies = $this->getDoctrine()->getRepository("App:Dialogreply")->findAllforRepair($rpid);



    return $this->render('repair/fixershow.html.twig', array(
      'access'=>$access,
      "dialogs"=>$dialogs,
      'rpid'=>$rpid,
      'script'=>$script[0],
      'repair'=>$arepair,
      'replies'=>$replies,
      'returnlink'=>'/',
      ));
  }


  public function update($rpid, $step, MessageController  $messenger)
  {
    $dialogs = $this->getDoctrine()->getRepository("App:Dialog")->findAll();
    $arepair = $this->getDoctrine()->getRepository("App:Repair")->findOne($rpid);
    $dialog =  $this->getDoctrine()->getRepository("App:Dialog")->findOnebyName($step);
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
      $olddata = json_decode($areply->getDialogreply());
      $areply->setDialogreply(json_encode($data));

      $areply->setUpdated($date);
      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->persist($areply);
      $entityManager->flush();
      if($step=="client")
      {
        $email = $data["email"];
        $name=   $data["name"];
        if(is_null($email)|| strlen($email) <10)
        {
          $this->addFlash('flash-message'," Error in email address or absent");
          return $this->redirectToRoute('repair_edit', ['rpid' => $rpid,'step'=>$step]);
        }
        dump($olddata);
        if(is_null($olddata) or ($email != $olddata->email))
        {
          $this->addFlash('flash-message'," Reply to a new email to confirm changed Email address ");
          $arepair->setRepairkey(mt_rand(100000, 999999));
          $arepair->setEmail($email);
          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->persist($arepair);
          $entityManager->flush();
          $messenger->sendRepairEmail($arepair );
          return $this->redirectToRoute('repair_edit', ['rpid' => $rpid,'step'=>$step]);
        }

      }
    }

    if($dialog->getDnext() && $dialog->getDnext()!= "")
    {
      $step = $dialog->getDnext();
      dump($step);
    }
    return $this->redirectToRoute('repair_edit', ['rpid' => $rpid,'step'=>$step,]);



  }




  public function uploadImage(Request $request, string $repairimagesfolder,
  FileUploader $uploader)
  {
    //directory
    $target_dir =  $repairimagesfolder;

    //catch the image sent from client
    $imagedata = $_POST['image'];

    //replacing some characters from the base64

    $imagedata = str_replace('data:image/png;base64,', '', $imagedata);
    $imagedata = str_replace('data:image/jpeg;base64,', '', $imagedata);
    $imagedata = str_replace(' ', '+', $imagedata);

    //decoding the base64
    $data = base64_decode($imagedata);

    //generating and unique name (or write manually one name)
    $unique = uniqid();

    //setting the path
    $filename = $unique.'.png';
    $filepath = $target_dir.$filename;

    //putting all the content into a file
    $success = file_put_contents($filepath, $data);

    $rpid = $request->get("rpid");
    $step = $request->get("step");

    $image = new image();
    if ($filename)
    {
      $image->setImagefilepath($filename);
      $image->setRepairId($rpid);
      $image->setStep($step);
      $image->setUpdated(new \DateTime());
      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->persist($image);
      $entityManager->flush();

    }

    return $this->redirectToRoute('repair_edit', ['rpid' => $rpid,'step'=>$step,]);
  }



  public function upload(UploadedFile $file,$target, $name)
  {

    try {
      if(!is_null($file))
      {
        $fileName = $name.'-'.uniqid().'.'.'jpg';
        $file->move($target, $fileName);
      }
    } catch (Exception $e) {
      $this->addFlash('flash-message'," Error  in upload , is file too big?");
      return null;
    }

    return $fileName;
  }

  public function deleteimage ($imgid)
  {
    $image =  $this->getDoctrine()->getRepository("App:Image")->findOne($imgid);
    dump($image);
    $rpid= $image->getRepairId();
    $step = $image->getStep();
    $entityManager = $this->getDoctrine()->getManager();
    $entityManager->remove($image);
    $entityManager->flush();
    return $this->redirectToRoute('repair_edit', ['rpid' => $rpid,'step'=>$step,]);
  }



}
