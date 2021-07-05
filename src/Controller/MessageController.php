<?php

namespace App\Controller;

//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Contracts\Translation\TranslatorInterface;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

use App\Entity\Message;
use App\Entity\Repair;
use App\Service\MyLibrary;
use App\Service\message_service;

use App\Controller\LinkrefController;
use App\Form\Type\MessageForm;
use App\Form\Type\UserMessageForm;
use App\Form\Type\VisitorMessageForm;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


use Symfony\Component\HttpFoundation\RequestStack;

class MessageController extends AbstractController
{


  private $requestStack ;
  private $mylib;
  private $mailer;
  private $params;


  public function __construct( MyLibrary $mylib,RequestStack $request_stack,ParameterBagInterface $params,MailerInterface $mailer)
  {
    $this->mylib = $mylib;
    $this->requestStack = $request_stack;
    $this->params = $params;
    $this->mailer = $mailer;
  }

  public function createMessageToAdmin(Request $request )
  {
    $user = $this->getUser();
    if($user)
    {
      return $this->createUserMessageToAdmin($request )  ;
    }
    else
    {
      return $this->createVisitorMessageToAdmin($request )  ;
    }
  }

  public function createUserMessageToAdmin(Request $request )
  {
    $user = $this->getUser();
    $message = new Message($this->getParameter('admin-name'), $this->getParameter('admin-email'),$user->getUsername(),$user->getEmail() ,"", "");
    $form = $this->createForm(UserMessageForm::class, $message);
    $form->handleRequest($request);
    if($form->isSubmitted() &&  $form->isValid())
    {
      $this->sendMessageToUserCopytoAdministrators($message,$user->getUserid(), $user->getLang());
      return $this->render('message/usermessage.html.twig',array(
        'message'=>$message,
        'returnlink' =>"/$this->lang/person/all")
        );
    } ;

    return $this->render('message/userform.html.twig', array(
      'lang'=>$this->lang,
      'form' => $form->createView(),
      ));
  }







  public function deleteMessage($cid)
  {

    $this->getDoctrine()->getRepository('App:Message')->delete($cid);
    return $this->redirect("/admin/message/all");

  }


  public function makeBulkMessage(int $sid)
  {


    $request = $this->requestStack->getCurrentRequest();
    $session = $request->getSession();
    $destinataires = $session->get('selectedusers');
    $userlist = explode(",",$destinataires);
    $numbertosend= count($userlist) - 1;
    return $this->render('message/bulkmessage.html.twig', array(
      'lang'=>$this->lang,
      'content'=>$content,
      'destinataires' =>$destinataires,
      'numbertosend'=>$numbertosend,
      'returnlink'=>'/admin/user/search',
      'actionlink'=>'/admin/message/bulk/send/'.$sid,
      ));
  }

  public function sendBulkMessage(int $sid,Request $request )
  {
    $request = $this->requestStack->getCurrentRequest();
    $session = $request->getSession();
    $destinataires = $session->get('selectedusers');
    $userlist = explode(",",$destinataires);
    $sendtoemailstr= "";
    foreach($userlist as $uid)
    {
      $user =  $this->getDoctrine()->getRepository('App:User')->findOne($uid);
      if($user)
      {
        $lang =  $user->getLang();

        $content =  $this->getDoctrine()->getRepository('App:Content')->findContentLang($sid,$lang);
        $subject = $content->gettitle();
        $body= $content->gettext();
        $sendtoemailstr .= $user->getemail().", ";
        $sendtoname = "group.email";
        $message = new message($user->getUserName(),$user->getEmail(),$this->getParameter('admin-name'), $this->getParameter('admin-email'),$subject, $body);
        $datesent =new \DateTime();
        $message->setDate_sent( $datesent);
        $footer =  $this->renderView('message/template/'.$lang.'/contentemailfooter.html.twig',array('userid'=> $uid ,'subjectid'=>$sid ,),'text/html');
        $userbody =    $this->renderView('message/template/'.$lang.'/emailfull.html.twig',array(
          'message'=>$message,'footer'=>$footer,),'text/html');

          $smessage = $this->makeSwiftMessage($message, $userbody);
          $this->mailer->send($smessage);

      }
    }
    $sn = $this->getDoctrine()->getManager();
    $message->setBcc( $sendtoemailstr);
    $sn -> persist($message);
    $sn -> flush();
    // $this->get('mailer')->send($smessage);


    return $this->render('message/bulkusermessage_ack.html.twig',array(
      'message'=>$message,
      'returnlink'=>'/admin/user/search',
      ));

  }





  function sendMessage($sender,$destination,$subject,$formattedbody)
  {
    $to = $destination['email'];
    $to = "paul.a.golder@lerot.org";
    $smessage = (new Email());
    $smessage->subject($subject);
    $smessage->from(new Address($sender['email'],$sender['name']));
    $smessage->to(new Address($destination['email'],$destination['name']));
    $smessage->html($formattedbody);
    //   $smessage->setContentType("text/html");
    $this->mailer->send($smessage);
  }




  function sendMessageToAdmin($message,$lang)
  {
    $datesent =new \DateTime();
    $message->setDate_sent( $datesent);
    $sn = $this->getDoctrine()->getManager();
    $sn -> persist($message);
    $sn -> flush();
    $adminfooter =  $this->renderView('message/template/'.$lang.'/adminemailfooter.html.twig',array(),'text/html');
    $formattedbody =    $this->renderView('message/template/'.$lang.'/emailfull.html.twig',array(
      'message'=>$message,'footer'=>$adminfooter,),'text/html');

      $smessage = $this->makeSwiftMessage($message, $formattedbody);
      $this->mailer->send($smessage);
  }

  function sendMessageToUser($message,$userid)
  {
    $datesent =new \DateTime();
    $message->setDate_sent( $datesent);
    $sn = $this->getDoctrine()->getManager();
    $sn -> persist($message);
    $sn -> flush();
    $userfooter =  $this->renderView('message/template/useremailfooter.html.twig',
    array('userid'=>$userid,),'text/html');
    $formattedbody =    $this->renderView('message/template/emailfull.html.twig',
    array('message'=>$message,'footer'=>$userfooter,),'text/html');
    $smessage = $this->makeSwiftMessage($message, $formattedbody);
    $this->mailer->send($smessage);
  }



  function sendUserMessage($subjecttag,$bodytag,$user)
  {
    $destination =  $user->getEmail();
    $destination = "paul.a.golder@lerot.org";
    $adminlang = $this->requestStack->getCurrentRequest()->getLocale();
    $body =  $this->renderView('message/template/'.$bodytag.'.html.twig',array('user'=> $user));
    $subject = $subjecttag;
    $message = new message($user->getUsername(),$destination,$this->getParameter('admin-name'), $this->getParameter('admin-email'),$subject, $body);
    $this->mailer->send($smessage);


  }


  function sendAdminMessage($subjecttag,$bodytag,$user,$lang)
  {
    $abody =  $this->renderView('message/template/'.$lang.'/'.$bodytag.'.html.twig',array('user'=> $user));
    $subject = $this->trans->trans($subjecttag,[],"messages",$lang);
    $amessage = new message($user->getUsername(),$user->getEmail(),$this->getParameter('admin-name'), $this->getParameter('admin-email'),$subject, $abody);
    $asmessage = $this->sendMessageToAdmin($amessage, $lang);
  }

  /* new functions */

  public function sendRepairEmail($arepair)
  {
    $email = $arepair->getEmail();
    $email ="paul.a.golder@lerot.org"; // overrive for testing
    $date = new \DateTime();
    $sender['email'] = $this->getParameter('admin-email');
    $sender['name'] = $this->getParameter('admin-name');
    $destination['name'] = $arepair->getEmail();
    $destination['email'] = $email;
    $messageparameters['sender'] = $sender;
    $messageparameters['destination']= $destination;
    $messageparameters['date'] = $date;
    $header =  $this->renderView('message/template/emailheader.html.twig',[],'text/html');
    $footer =  $this->renderView('message/template/emailfooter.html.twig',[],'text/html');
    $body =  $this->renderView('message/template/emailconfirmrequest.html.twig',['arepair'=>$arepair,],'text/html');
    $messagebody = $this->renderView('message/template/emailfull.html.twig',array(
      'message'=>$messageparameters,'header'=>$header,'body'=>$body,'footer'=>$footer,),'text/html');
     $this->sendMessage($sender,$destination,"Repair Email Address Confirmation",$messagebody);
  }





  function captchaverify($recaptcha)
  {
    $secret = $this->getParameter('recaptcha_secret');
    $url = "https://www.google.com/recaptcha/api/siteverify";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array(
      "secret"=>$secret,"response"=>$recaptcha));
      $response = curl_exec($ch);
      curl_close($ch);
      $data = json_decode($response);

      return $data->success;
      //return true;
  }
}
