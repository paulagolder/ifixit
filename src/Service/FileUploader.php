<?php

// src/AppBundle/Service/FileUploader.php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private $targetDir;

    public function __construct($targetDir)
    {
        $this->targetDir = $targetDir;
    }

    public function xupload(UploadedFile $file)
    {
        $type=  $file->getMimeType();
        $fileName = md5(uniqid()).'.'.$file->guessExtension();
        $file->move($this->getTargetDir(), $fileName);
        return $fileName;
    }

    public function getTargetDir()
    {
        return $this->targetDir;
    }
}
