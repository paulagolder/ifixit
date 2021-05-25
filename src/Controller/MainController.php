<?php
// src/Controller/Main/MainController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MainController extends AbstractController
{


    public function Overview()
    {
        $date = $objDateTime = new \DateTime('NOW');
        $repairs =  $this->getDoctrine()->getRepository("App:Repair")->findAll();
        dump($repairs);
        return $this->render('main/overview.html.twig', [
            'date' => $date,
            'repairs'=>$repairs,
        ]);;
    }

}
