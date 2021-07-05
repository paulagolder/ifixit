<?php

// src/App/Service/FileUploader.php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;






class FileUploader
{
    private $targetDirectory;
    private $slugger;

    public function __construct($targetDirectory, SluggerInterface $slugger)
    {
        $this->targetDirectory = $targetDirectory;
        $this->slugger = $slugger;
    }

   public function upload($repairimagesfolder, $file, $filename)
    {
        try
        {
            $file->move($repairimagesfolder, $filename);
        } catch (FileException $e)
        {
            throw new FileException('Failed to upload file');
        }
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}
