<?php

// src/Controller/RegistrationController.php
namespace App\Controller;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bridge\Twig\Mime\NotificationEmail;

use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

use App\Form\Type\UserForm;
use App\Form\Type\UserRegForm;
use App\Form\Type\ResetForm;
use App\Form\Type\CompleteRegForm;
use App\Form\Type\ConfirmEmailForm;

use App\Entity\User;
use App\Entity\Message;
use App\Service\MyLibrary;

use App\Controller\LinkrefController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\EventListener\ImageUploadListener;

class RegistrationController extends AbstractController
{


    private $mylib;
    private $requestStack ;
    private $encoderFactory;
    private $trans;
    private $parameters;
    private $messenger;

    public function __construct( MyLibrary $mylib ,RequestStack $request_stack,EncoderFactoryInterface $encoderFactory,ParameterBagInterface $parameters, MessageController  $messenger)
    {
        $this->mylib = $mylib;
        $this->requestStack = $request_stack;
        $this->encoderFactory = $encoderFactory;
        $this->parameters = $parameters;
        $this->messenger = $messenger;
    }

    //======================================  regisrtation stage 1  ===============================================
    // user registers creates user record and recieves email to confirm email address
    // user confirms email address either by clicking on link or by logging in

    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $date = new \DateTime();
        $message = "";
        $fixer = new User();
        $form = $this->createForm(UserRegForm::class, $fixer);
        $form->handleRequest($request);
        if($this->getDoctrine()->getRepository("App:User")->isUniqueName($fixer->getNickname()))
        {
            if ($form->isSubmitted() && $form->isValid() )
            {
                $encoder = $this->encoderFactory->getEncoder(new User());
                $plainpassword = $fixer->getPlainPassword();
                $hashpassword = $encoder->encodePassword($plainpassword, null);
                $fixer->setPassword($hashpassword);
                $fixer->setLastlogin($date);
                $fixer->addRole('ROLE_FIXER');
                $fixer->setFixerkey("".mt_rand(100000, 999999));
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($fixer);
                $entityManager->flush();
                //message to user
                dump($fixer);
               $sender['email'] = $this->getParameter('admin-email');
               $sender['name'] = $this->getParameter('admin-name');
               $destination['name'] = $fixer->getNickname();
               $destination['email'] = $fixer->getEmail();
               $messageparameters['sender'] = $sender;
               $messageparameters['destination']= $destination;
               $messageparameters['date'] = $date;
               $header =  $this->renderView('message/template/emailheader.html.twig',[],'text/html');
               $footer =  $this->renderView('message/template/contentemailfooter.html.twig',array('userid'=> $fixer->getNickname() ,'subjectid'=>"stuff" ,),'text/html');
               $body ="<div>Registration successful please click link to confirm email address</div>";
               $messagebody =    $this->renderView('message/template/emailfull.html.twig',array(
          'message'=>$messageparameters,'header'=>$header,'body'=>$body,'footer'=>$footer,),'text/html');

                $this->messenger->sendMessage($sender,$destination,"Registration Successful",$messagebody);
                //notice to screen
                $message = [];
                $message[] = 'You have commenced registration';
                $message[]= 'Reply to the email tyou recieve to confirm your email address';
                $message[]= 'The administator will them approve your registration';
                return $this->render('main/message.html.twig',
                array(
                    'user' => $fixer ,
                    'messages'=>$message,
                    'heading'=> 'registration.started',
                    'continue'=>"/fixers"
                    ));
            }
        }
        else
        {
            $message= "duplicate.username";
        }
        return $this->render(
            'security/register.html.twig',
            array('form' => $form->createView() ,
            'message'=>$message,
            'returnlink'=>"/register",
            ));
    }


    //======================================  registation stage 2  ===============================================
    // user validates email then  admin is sent request for approval  and message to user saying to await admin approval

    public function confirmemail(Request $request, $uid)
    {
        $lang = $this->requestStack->getCurrentRequest()->getLocale();
        $fixer =   $this->getDoctrine()->getRepository("App:User")->findOne($uid);
        $uname = $fixer->getUsername();
        $uemail = $fixer->getEmail();

        $form = $this->createForm(ConfirmEmailForm::class, $fixer);
        $form->handleRequest($request);
        $codeisvalid= $fixer->codeisvalid();
        $temp = $fixer->hasRole("ROLE_AEMC");
        if ($form->isSubmitted() && $form->isValid() && $codeisvalid && $temp )
        {
            $fixer->setLastlogin( new \DateTime());
            $fixer->updateRole("emailconfirmed");
            $fixer->setUsername($uname);
            $fixer->setEmail($uemail);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($fixer);
            $entityManager->flush();
            //message to user
            $smessage = $this->messenger->sendUserMessage('email.confirmed','emailvalidationcomplete',$fixer);
            // message to admin
            $amessage = $this->messenger->sendAdminMessage('approbation.request','approbationrequest',$fixer,$lang);
            //clear token
            $this->get('session')->invalidate();
            $token = new UsernamePasswordToken($fixer, null, 'main', $fixer->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_main', serialize($token));
            return $this->render('registration/done.html.twig',
            array(
                'user' => $fixer ,
                'heading'=>'email.confirmed',
                'messages'=>'',

                ));
        }

        return $this->render(
            'registration/complete.html.twig',
            array('form' => $form->createView() , 'lang'=>$lang,)
            );
    }





    public function remoteconfirmemail($uid, $code)
    {
        $lang = $this->requestStack->getCurrentRequest()->getLocale();
        $fixer =   $this->getDoctrine()->getRepository("App:User")->findOne($uid);
        if($fixer)
        {
            $fixercode = $fixer->getRegistrationCode();
            $temp = $fixer->hasRole("ROLE_AEMC");
            if($temp )
            {
                if($code == $fixercode  )
                {
                    $fixer->setLastlogin( new \DateTime());
                    $fixer->updateRole("emailconfirmed");
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($fixer);
                    $entityManager->flush();
                    //message to user
                    $smessage = $this->messenger->sendUserMessage('email.confirmed','emailvalidationcomplete',$fixer);
                    // message to admin
                    $amessage = $this->messenger->sendAdminMessage('approbation.request','approbationrequest',$fixer,$lang);
                    //clear token
                    $token = new UsernamePasswordToken($fixer, null, 'main', $fixer->getRoles());
                    $this->get('security.token_storage')->setToken($token);
                    $this->get('session')->set('_security_main', serialize($token));
                    return $this->render('registration/done.html.twig',
                    array(
                        'user' => $fixer,
                        'heading'=>'email.confirmed',
                        'messages'=>"",

                        ));
                }
                return $this->render('registration/done.html.twig',
                array(
                    'user' => $fixer,
                    'heading'=>'failed.to.confirm.email',
                    'messages'=>'',

                    ));

            }
            else
            {
            return $this->render('registration/done.html.twig',
            array(
                'user' => $fixer,
                'heading'=>'already.confirmed.email',
                'messages'=>'',

                ));
            }

        }

        return $this->redirect('/accueil/message/'.'user.error');

    }


    public function approveuser($uid)
    {
        $lang = $this->requestStack->getCurrentRequest()->getLocale();
        $fixer =   $this->getDoctrine()->getRepository("App:User")->findOne($uid);
        $chpw = $fixer->hasRole("ROLE_AADA");
        $nomemb = ($fixer->getMembership() =="" or $fixer->getMembership() == null );
        if($chpw or $nomemb)
        {
            $fixer->setLastlogin( new \DateTime());
            $fixer->updateRole("userapproved");
            $adminuser = $this->getUser();
            $time = new \DateTime();
            $fixer->setContributor($adminuser->getUsername());
            $fixer->setUpdate_Dt($time);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($fixer);
            $entityManager->flush();
            $smessage = $this->messenger->sendUserMessage('registration.complete','registrationcompletion',$fixer);
            return $this->redirect("/admin/user/".$uid);
        }
        return $this->redirect("/admin/user/".$uid);

    }

    public function rejectuser($uid)
    {

        $fixer =   $this->getDoctrine()->getRepository("App:User")->findOne($uid);
        $chpw = $fixer->hasRole("ROLE_AADA");
        $nomemb = ($fixer->getMembership() =="" or $fixer->getMembership() == null );
        if($chpw or $nomemb)
        {
            $fixer->setLastlogin( new \DateTime());
            $fixer->updateRole("userrejected");
            $adminuser = $this->getUser();
            $time = new \DateTime();
            $fixer->setContributor($adminuser->getUsername());
            $fixer->setUpdate_Dt($time);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($fixer);
            $entityManager->flush();
            $smessage = $this->messenger->sendUserMessage('registration.rejected','registrationrejected',$fixer);
            return $this->redirect("/admin/user/".$uid);
        }
        return $this->redirect("/admin/user/".$uid);

    }



    //======================================  reregistation forced by administrator  ===============================================

    public function remotereregister($uid, $code, Request $request)
    {
        $lang = $this->requestStack->getCurrentRequest()->getLocale();
        $fixer =   $this->getDoctrine()->getRepository("App:User")->findOne($uid);
        # $lang = $fixer->getLocale();
        $fixercode = $fixer->getRegistrationCode();
        $temp = $fixer->hasRole("ROLE_APWC");
        if($code == $fixercode && $temp)
        {
            $fixer->setLastlogin( new \DateTime());
            $fixer->updateRole("reregistration");
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($fixer);
            $entityManager->flush();
            //message to user
            $smessage = $this->messenger->sendUserMessage('email.confirmed','emailvalidationcomplete',$fixer);
            // message to admin
            $amessage = $this->messenger->sendAdminMessage('approbation.request','approbationrequest',$fixer,$lang);
            //clear token
            $token = new UsernamePasswordToken($fixer, null, 'main', $fixer->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_main', serialize($token));
            return $this->render('registration/done.html.twig',
            array(
                'user' => $fixer ,
                'heading'=>'email.confirmed',
                'messages'=>'',

                ));


        }

        return $this->render('registration/reregfail.html.twig',
        array(
            'username' => $fixer->getUsername() ,
            'email' => $fixer->getEmail()

            ));
    }




    //====================================== password reset ===============================================


    public function resetpasswordrequest(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {

        $fixer = new User();
        $form = $this->createForm(ResetForm::class, $fixer);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()  && $this->captchaverify($request->get('g-recaptcha-response')) )
        {
            $email = $fixer->getEmail();
            $ouser =   $this->getDoctrine()->getRepository("App:User")->loadUserbyUsername($email);
            if(!$ouser)
            {
                return $this->render(
                    'registration/reset.html.twig',
                    array('form' => $form->createView() ,
                    'lang'=>$this->lang,
                    'message' => "user.not.recognised",)
                    );
            }
            $ouser->setLastlogin( new \DateTime());
            $ouser->updateRole("newpasswordrequest");
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ouser);
            $entityManager->flush();
        //    $smessage = $this->messenger->sendUserMessage('request.new.password','resetpassword_notice',$ouser,$this- >lang);
            $smessage =$this->forward('App\Controller\MessageController::sendUserMessage', [
        'subjecttag' => 'request.new.password',
        'bodytag' => 'resetpassword_notice',
         'user' => $ouser ,]);
             $message = array();
            $message[] =    'you.have.sucessfully.requested.change.password';
            $message[] =    'to.complete.reply.to.email';
            return $this->render('registration/done.html.twig',
            array(
                'user' => $ouser ,
                'messages'=> $message,
                'heading'=> 'request.new.password',
                ));
        }

        return $this->render('registration/reset.html.twig',
        array(
            'form' => $form->createView() ,
            'lang'=>$this->lang,
            'message'=>null,));
    }

    public function remotechangepassword($uid, $code, Request $request)
    {

        $fixer =   $this->getDoctrine()->getRepository("App:User")->findOne($uid);
        $fixercode = $fixer->getRegistrationCode();
        $chpw = $fixer->hasRole("ROLE_APWC");
        if($code == $fixercode && $chpw)
        {
            $fixer->setLastlogin( new \DateTime());
            $fixer->updateRole("passwordchanged");
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($fixer);
            $entityManager->flush();
            $fixer =   $this->getDoctrine()->getRepository("App:User")->findOne($uid);
            if (!$fixer) {
                return $this->render(
                    'registration/reset.html.twig',
                    array('form' => $form->createView() ,
                    'lang'=>$this->lang,
                    'message' => "user.not.recognised",)
                    );
            } else {

                $token = new UsernamePasswordToken($fixer, null, 'main', $fixer->getRoles());
                $this->get('security.token_storage')->setToken($token);
                $this->get('session')->set('_security_main', serialize($token));
                return $this->redirect("/".$fixer->getlang()."/userpassword/".$uid);
            }

        }

        return $this->render('registration/reregfail.html.twig',
        array(
            'username' => $fixer->getUsername() ,
            'email' => $fixer->getEmail()

            ));
    }




    //====================================== captcha verify  ===============================================


    function captchaverify($recaptcha)
    {

        $secret = $this->parameters->get('recaptcha_secret');
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
            // return true;
    }

}
