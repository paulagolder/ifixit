<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\FileUploader;

use App\Entity\Image;



class UploadController extends AbstractController
{



    public function index(Request $request,
                          FileUploader $uploader)
    {

        $repairimagesfolder = $this->getParameter('repair-images-folder');
        $token = $request->get("utoken");
        $rpid = $request->get("rpid");
            $step = $request->get("step");

        $file = $request->files->get('fileToUpload');
        if (empty($file))
        {
            return new Response("No file specified",
               Response::HTTP_UNPROCESSABLE_ENTITY, ['content-type' => 'text/plain']);
        }
         $filename = $file->getClientOriginalName();

        $uploader->upload($repairimagesfolder, $file, $filename);
        $image = new $image();
        $image->setRpid($rpid);
        $image->setStep($step);
        $roadgroup->setImagefile($filename);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($image );
        $entityManager->flush();
        $this->addFlash(
            'notice',
            'Image '.$filename.' uploaded!'.$token
        );

           return $this->redirect("/repair/edit/$rpid/$step");
    }
}
