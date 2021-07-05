<?php

namespace App\Entity;

use App\Repository\ImageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ImageRepository::class)
 */
class Image
{
     /**
     * @ORM\Id
     * @ORM\Column(name="imageid",type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $ImageId;



    /**
     * @ORM\Column(name="repairid",type="integer")
     */
    private $RepairId;

    /**
     * @ORM\Column(name="step",type="string", length=50)
     */
    private $Step;


    /**
     * @ORM\Column(name="imagefilepath",type="string", length=50)
     */
    private $Imagefilepath;

    /**
     * @ORM\Column(name="updated",type="datetime")
     */
    private $Updated;

      public function getImageId()
    {
        return $this->ImageId;
    }

    public function setImageId($number)
    {
        $this->ImageId= $number;
        return $this;
    }

    public function getRepairId()
    {
        return $this->RepairId;
    }

    public function setRepairId($number)
    {
        $this->RepairId = $number;
        return $this;
    }

    public function getStep()
    {
        return $this->Step;
    }

    public function setStep(string $text)
    {
        $this->Step = $text;
        return $this;
    }

   public function getImagefilepath()
    {
        return $this->Imagefilepath;
    }

    public function setImagefilepath(string $text)
    {
        $this->Imagefilepath = $text;
        return $this;
    }


    public function getUpdated()
    {
        return $this->Updated;
    }

    public function setUpdated($date)
    {
        $this->Updated = $date;
        return $this;
    }

}

