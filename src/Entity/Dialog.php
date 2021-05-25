<?php

namespace App\Entity;

use App\Repository\DialogRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DialogRepository::class)
 */
class Dialog
{
     /**
     * @ORM\Id
     * @ORM\Column(name="dialogid",type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $DialogId;

    /**
     * @ORM\Column(name="dname",type="string", length=20)
     */
    private $Dname;


    /**
     * @ORM\Column(name="dtext",type="string", length=200)
     */
    private $Dtext;


        /**
     * @ORM\Column(name="dhelp",type="string", length=200)
     */
    private $Dhelp;

    /**
     * @ORM\Column(name="dnext",type="string", length=200)
     */
    private $Dnext;

        /**
     * @ORM\Column(name="dfields",type="json_array", length=500)
     */
    private $Dfields;


    public function getDialogId()
    {
        return $this->DialogId;
    }

    public function setDialogId($number)
    {
        $this->DialogId = $number;
        return $this;
    }

    public function getDname()
    {
        return $this->Dname;
    }

    public function setDname(string $text)
    {
        $this->Dname = $text;
        return $this;
    }

     public function getDtext()
    {
        return $this->Dtext;
    }

    public function setDtext(string $text)
    {
        $this->Dtext = $text;
        return $this;
    }

   public function getDhelp()
    {
        return $this->Dhelp;
    }

    public function setDhelp(string $text)
    {
        $this->Dhelp = $text;
        return $this;
    }

     public function getDnext()
    {
        return $this->Dnext;
    }

    public function setDnext(string $text)
    {
        $this->Dnext = $text;
        return $this;
    }

      public function getDfields()
    {
        return $this->Dfields;
    }

    public function setDfields(string $text)
    {
        $this->Dtext = $fields;
        return $this;
    }
}

