<?php

namespace App\Entity;

use App\Repository\RepairRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RepairRepository::class)
 */
class Repair
{
     /**
     * @ORM\Id
     * @ORM\Column(name="repairid",type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $RepairId;

    /**
     * @ORM\Column(name="name",type="string", length=50)
     */
    private $Name;


        /**
     * @ORM\Column(name="email",type="string", length=50)
     */
    private $Email;


        /**
     * @ORM\Column(name="repairkey",type="string", length=50)
     */
    private $Repairkey;


           /**
     * @ORM\Column(name="tempkey",type="string", length=50)
     */
    private $Tempkey;

        /**
     * @ORM\Column(name="script",type="text", length=50)
     */
    private $Script;

    /**
     * @ORM\Column(name="updated",type="datetime")
     */
    private $Updated;

    public function getRepairId()
    {
        return $this->RepairId;
    }

    public function setRepairId($number)
    {
        $this->RepairId = $number;
        return $this;
    }

    public function getName()
    {
        return $this->Name;
    }

    public function setName(string $Name)
    {
        $this->Name = $Name;
        return $this;
    }

      public function getEmail()
    {
        return $this->Email;
    }

    public function setEmail(string $text)
    {
        $this->Email = $text;
        return $this;
    }

      public function getRepairkey()
    {
        return $this->Repairkey;
    }

    public function setRepairkey(string $text)
    {
        $this->Repairkey = $text;
        return $this;
    }

       public function getTempkey()
    {
        return $this->Tempkey;
    }

    public function setTempkey(string $text)
    {
        $this->Tempkey = $text;
        return $this;
    }

     public function getScript()
    {
        return $this->Script;
    }

    public function setScript(string $text)
    {
        $this->Script = $text;
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

