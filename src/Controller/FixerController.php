<?php
// src/Controller/Main/MainController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Reply;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use App\Form\Type\FixerForm;
use App\Entity\Repair;
use App\Entity\Track;
use App\Entity\User;
use App\Entity\Dialogreply;
use App\Service\MyLibrary;

class FixerController extends AbstractController
{
  private $requestStack;
  private $defaultscript;
  private $mylib;



  private $encoderFactory;


  public function __construct( MyLibrary $mylib ,RequestStack $request_stack,EncoderFactoryInterface $encoderFactory)
  {
    $this->mylib = $mylib;
    $this->requestStack = $request_stack;
    $this->encoderFactory = $encoderFactory;
  }


  public function fixerview($fxid)
  {

    $request = $this->requestStack->getCurrentRequest();
    if($fxid)
    {
      $afixer = $this->getDoctrine()->getRepository("App:User")->findOne($fxid);
      $repairs= $this->getDoctrine()->getRepository("App:Repair")->findCurrent($fxid);
    }
   dump($repairs);
    return $this->render('fixer/fixersummary.html.twig', array(

      'fixer'=>$afixer,
      'repairs'=>$repairs,
      'returnlink'=>"/",
      ));
  }

  public function overview($fxid)
  {
      $afixer = $this->getDoctrine()->getRepository("App:User")->findOne($fxid);
      $following= $this->getDoctrine()->getRepository("App:Repair")->findCurrent($fxid);

    dump($following);
    return $this->render('fixer/overview.html.twig', array(

      'fixer'=>$afixer,
      'tracks'=>$following,
      'returnlink'=>"/",
      ));
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

  public function fixernew()
  {
    return fixeredit(0);
  }


  public function fixeredit($fxid)
  {

    if($fxid < 1)
    {
      $fixer = new Fixer();
      $fixer->setNickname("nickname");
      $fixer->setFullname("fullname");
      $fixer->setEmail("dummy@dummy.com");
      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->persist($fixer);
      $entityManager->flush();
      $fxid = $fixer->getFixerId();
    }
    $request = $this->requestStack->getCurrentRequest();
    $fixer = $this->getDoctrine()->getRepository('App:User')->findOne($fxid);
    $encoder = $this->encoderFactory->getEncoder($fixer);
    $tpass= $fixer->getEmail();

    $form = $this->createForm(FixerForm::class, $fixer);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid())
    {
      $entityManager = $this->getDoctrine()->getManager();
      $plainpassword = $fixer->getPlainPassword();
      $hashpassword = $encoder->encodePassword($plainpassword,null);
      $fixer->setPassword($hashpassword);
      $entityManager->persist($fixer);
      $entityManager->flush();
      return $this->redirect("/fixer/".$fxid);
    }

    $password = $fixer->getPassword();

    return $this->render('fixer/fixeredit.html.twig', array(
      'form' => $form->createView(),
      'password' => $fixer->getPassword(),
      'returnlink'=> "/fixer/".$fxid,
      ));
  }


  public function login_action()
  {
    $request = $this->requestStack->getCurrentRequest();
    $email = $_POST['email'];
    $plainpassword = $_POST['password'];
    dump($plainpassword);
    dump($email);
    $user = $this->getDoctrine()->getRepository('App:User')->findFixerByEmail($email);
    if($user)
    {
      $encoder = $this->encoderFactory->getEncoder($user);
      $pw = $user->getPassword();
      dump($pw);

      if($encoder->isPasswordValid($pw, $plainpassword, null))
      {
        dump(" password matches");
        $entityManager = $this->getDoctrine()->getManager();
        $fixerkey =  date('YmdHis');
        $user->setFixerkey($fixerkey);
        $user->setLastlogin( new \DateTime());
        $entityManager->persist($user);
        $entityManager->flush();
        dump($user);
        $cookie = new Cookie
        (
          'fixerkey',	// Cookie name.
          $fixerkey,	// Cookie value.
          time() + ( 6 * 30 * 24 * 60 * 60)	// Expires 1 month.
          );
          $res = new Response();
          $res->headers->setCookie( $cookie );
          $res->send();
        return $this->redirect("/fixer/overview/".$user->getID());
      }
      else
      {
        $this->addFlash('flash-message'," Login failed ");
        return $this->render('fixer/login.html.twig', array('returnlink'=> "/", ));
      }
    }
    else
    {
      $this->addFlash('flash-message'," Email unknown ");
      return $this->render('fixer/login.html.twig', array('returnlink'=> "/", ));
    }

  }


  public function login()
  {
    return $this->render('fixer/login.html.twig', array('returnlink'=> "/", ));
  }

}
