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

use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

use App\Form\Type\UserRegForm;
use App\Form\Type\ResetForm;
use App\Form\Type\CompleteRegForm;
use App\Form\Type\ConfirmEmailForm;

use App\Entity\User;
use App\Entity\Message;
use App\Service\MyLibrary;

use App\Controller\MessageController;

use App\Controller\LinkrefController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\EventListener\ImageUploadListener;

class RegistrationControllerb extends AbstractController
{

    private $lang="fr";
    private $mylib;
    private $requestStack ;
    private $encoderFactory;
    private $trans;
    private $parameters;
    private $messenger;

    public function __construct( MyLibrary $mylib ,RequestStack $request_stack,EncoderFactoryInterface $encoderFactory,TranslatorInterface $translator,ParameterBagInterface $parameters, MessageController  $messenger)
    {
        $this->mylib = $mylib;
        $this->requestStack = $request_stack;
        $this->encoderFactory = $encoderFactory;
        $this->trans =$translator;
        $this->parameters = $parameters;
        $this->messenger = $messenger;

    }

    //======================================  registation stage 1  ===============================================
    // user registers creates user record and recieves email to confirm email address
    // user confirms email address either by clicking on link or by logging in

    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $lang = $this->requestStack->getCurrentRequest()->getLocale();
        $message = "";
        $user = new User();
        $form = $this->createForm(UserRegForm::class, $user);
        $form->handleRequest($request);
        if($this->getDoctrine()->getRepository("App:User")->isUniqueName($user->getEmail()))
        {
            if ($form->isSubmitted() && $form->isValid()  && $this->captchaverify($request->get('g-recaptcha-response')) )
            {
                $encoder = $this->encoderFactory->getEncoder($user);
                $plainpassword = $user->getPlainPassword();
                $hashpassword = $encoder->encodePassword($plainpassword,null);
                $user->setPassword($hashpassword);
                $user->setLastlogin( new \DateTime());
                $user->updateRole("createuser");
                $user->setLocale( $lang );
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
                //message to user
                $smessage = $this->messenger->sendConfidentialUserMessage('registration.success','cond_reg_notice',$user);
                //notice to screen
                $message = array();
                $message[] = 'you.have.commenced.registration';
                $message[] = 'to.continue.reply.to.email';
                return $this->render('registration/done.html.twig',
                array(
                    'user' => $user ,
                    'messages'=>$message,
                    'heading'=> 'registration.started',
                    ));
            }
        }
        else{
            $message= "duplicate.username";
        }
        return $this->render(
            'registration/register.html.twig',
            array('form' => $form->createView() ,
            'lang'=>$this->lang,
            'message'=>$message,
            ));
    }

    //======================================  registation stage 2  ===============================================
    // user validates email then  admin is sent request for approval  and message to user saying to await admin approval

    public function confirmemail(Request $request, $uid)
    {
        $lang = $this->requestStack->getCurrentRequest()->getLocale();
        $user =   $this->getDoctrine()->getRepository("App:User")->findOne($uid);
        $uname = $user->getUsername();
        $uemail = $user->getEmail();

        $form = $this->createForm(ConfirmEmailForm::class, $user);
        $form->handleRequest($request);
        $codeisvalid= $user->codeisvalid();
        $temp = $user->hasRole("ROLE_AEMC");
        if ($form->isSubmitted() && $form->isValid() && $codeisvalid && $temp )
        {
            $user->setLastlogin( new \DateTime());
            $user->updateRole("emailconfirmed");
            $user->setUsername($uname);
            $user->setEmail($uemail);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            //message to user
            $smessage = $this->messenger->sendUserMessage('email.confirmed','emailvalidationcomplete',$user);
            // message to admin
            $amessage = $this->messenger->sendAdminMessage('approbation.request','approbationrequest',$user,$lang);
            //clear token
            $this->get('session')->invalidate();
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_main', serialize($token));
            return $this->render('registration/done.html.twig',
            array(
                'user' => $user ,
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
        $user =   $this->getDoctrine()->getRepository("App:User")->findOne($uid);
        if($user)
        {
            $usercode = $user->getRegistrationCode();
            $temp = $user->hasRole("ROLE_AEMC");
            if($temp )
            {
                if($code == $usercode  )
                {
                    $user->setLastlogin( new \DateTime());
                    $user->updateRole("emailconfirmed");
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($user);
                    $entityManager->flush();
                    //message to user
                    $smessage = $this->messenger->sendUserMessage('email.confirmed','emailvalidationcomplete',$user);
                    // message to admin
                    $amessage = $this->messenger->sendAdminMessage('approbation.request','approbationrequest',$user,$lang);
                    //clear token
                    $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                    $this->get('security.token_storage')->setToken($token);
                    $this->get('session')->set('_security_main', serialize($token));
                    return $this->render('registration/done.html.twig',
                    array(
                        'user' => $user,
                        'heading'=>'email.confirmed',
                        'messages'=>"",

                        ));
                }
                return $this->render('registration/done.html.twig',
                array(
                    'user' => $user,
                    'heading'=>'failed.to.confirm.email',
                    'messages'=>'',

                    ));
            }
            else
            {
            return $this->render('registration/done.html.twig',
            array(
                'user' => $user,
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
        $user =   $this->getDoctrine()->getRepository("App:User")->findOne($uid);
        $chpw = $user->hasRole("ROLE_AADA");
        $nomemb = ($user->getMembership() =="" or $user->getMembership() == null );
        if($chpw or $nomemb)
        {
            $user->setLastlogin( new \DateTime());
            $user->updateRole("userapproved");
            $adminuser = $this->getUser();
            $time = new \DateTime();
            $user->setContributor($adminuser->getUsername());
            $user->setUpdate_Dt($time);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            $smessage = $this->messenger->sendUserMessage('registration.complete','registrationcompletion',$user);
            return $this->redirect("/admin/user/".$uid);
        }
        return $this->redirect("/admin/user/".$uid);
    }

    public function rejectuser($uid)
    {

        $user =   $this->getDoctrine()->getRepository("App:User")->findOne($uid);
        $chpw = $user->hasRole("ROLE_AADA");
        $nomemb = ($user->getMembership() =="" or $user->getMembership() == null );
        if($chpw or $nomemb)
        {
            $user->setLastlogin( new \DateTime());
            $user->updateRole("userrejected");
            $adminuser = $this->getUser();
            $time = new \DateTime();
            $user->setContributor($adminuser->getUsername());
            $user->setUpdate_Dt($time);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            $smessage = $this->messenger->sendUserMessage('registration.rejected','registrationrejected',$user);
            return $this->redirect("/admin/user/".$uid);
        }
        return $this->redirect("/admin/user/".$uid);
    }



    //======================================  reregistation forced by administrator  ===============================================

    public function remotereregister($uid, $code, Request $request)
    {
        $lang = $this->requestStack->getCurrentRequest()->getLocale();
        $user =   $this->getDoctrine()->getRepository("App:User")->findOne($uid);
        # $lang = $user->getLocale();
        $usercode = $user->getRegistrationCode();
        $temp = $user->hasRole("ROLE_APWC");
        if($code == $usercode && $temp)
        {
            $user->setLastlogin( new \DateTime());
            $user->updateRole("reregistration");
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            //message to user
            $smessage = $this->messenger->sendUserMessage('email.confirmed','emailvalidationcomplete',$user);
            // message to admin
            $amessage = $this->messenger->sendAdminMessage('approbation.request','approbationrequest',$user,$lang);
            //clear token
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_main', serialize($token));
            return $this->render('registration/done.html.twig',
            array(
                'user' => $user ,
                'heading'=>'email.confirmed',
                'messages'=>'',

                ));
        }

        return $this->render('registration/reregfail.html.twig',
        array(
            'username' => $user->getUsername() ,
            'email' => $user->getEmail()

            ));
    }




    //====================================== password reset ===============================================


    public function resetpasswordrequest(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {

        $user = new User();
        $form = $this->createForm(ResetForm::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()  && $this->captchaverify($request->get('g-recaptcha-response')) )
        {
            $email = $user->getEmail();
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

        $user =   $this->getDoctrine()->getRepository("App:User")->findOne($uid);
        $usercode = $user->getRegistrationCode();
        $chpw = $user->hasRole("ROLE_APWC");
        if($code == $usercode && $chpw)
        {
            $user->setLastlogin( new \DateTime());
            $user->updateRole("passwordchanged");
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            $user =   $this->getDoctrine()->getRepository("App:User")->findOne($uid);
            if (!$user) {
                return $this->render(
                    'registration/reset.html.twig',
                    array('form' => $form->createView() ,
                    'lang'=>$this->lang,
                    'message' => "user.not.recognised",)
                    );
            } else {

                $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                $this->get('security.token_storage')->setToken($token);
                $this->get('session')->set('_security_main', serialize($token));
                return $this->redirect("/".$user->getlang()."/userpassword/".$uid);
            }

        }

        return $this->render('registration/reregfail.html.twig',
        array(
            'username' => $user->getUsername() ,
            'email' => $user->getEmail()

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
