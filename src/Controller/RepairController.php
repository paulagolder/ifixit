<?php
// src/Controller/Main/MainController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Reply;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Form\Type\RepairForm;
use App\Entity\Repair;
use App\Entity\Dialog;
use App\Entity\Dialogreply;

class RepairController extends AbstractController
{
    private $requestStack;


    public function __construct( RequestStack $request_stack)
    {
        $this->requestStack = $request_stack;
    }

    public function new()
    {
       $date = $objDateTime = new \DateTime('NOW');

        return $this->render('repair/edit.html.twig', [
            'date' => $date,
        ]);;
    }


    public function edit($rpid,$step)
    {
        $dialog = $this->getDoctrine()->getRepository("App:Dialog")->findOnebyName($step);
        dump($dialog);

        $request = $this->requestStack->getCurrentRequest();
        if($rpid)
        {
              $arepair = $this->getDoctrine()->getRepository("App:Repair")->findOne($rpid);
              $areply = $this->getDoctrine()->getRepository("App:Dialogreply")->findOne($rpid,$step);
              dump($arepair);
              dump($areply);
        }
        if(! isset($arepair))
        {
            $arepair= new repair();
            $arepair->setName("new repair add name");
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($arepair);
                $entityManager->flush();
                $rpid = $arepair->getRepairid();
        }

        return $this->render('repair/script.html.twig', array(
            "dialog"=>$dialog,
            "step" => $step,
            'rpid'=>$rpid,
            'repair'=>$arepair,
            'reply'=>$areply,
            'returnlink'=>'/',
            ));
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
        $fields= $dialog->getDfields();
        $data=[];
        foreach($fields as $field)
        {
        $data[$field["fname"]] = $request->get($field["fname"]);
        }
        dump($data);
           $areply->setDialogreply(json_encode($data));
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($areply);
                $entityManager->flush();
        }
        $step = $dialog->getDnext();
       return $this->redirectToRoute('repair_edit', ['rpid' => $rpid,'step'=>$step,]);


}

}
