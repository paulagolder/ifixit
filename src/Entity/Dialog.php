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
     * @ORM\Column(name="dname",type="string", length=200)
     */
    private $Dname;


    /**
     * @ORM\Column(name="dlabel",type="string", length=200)
     */
    private $Dlabel;


       /**
     * @ORM\Column(name="dprompt",type="string", length=200)
     */
    private $Dprompt;

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

    /**
     * @ORM\Column(name="dupdated",type="datetime")
     */
    private $Dupdated;


 public $Complete;

    public function getDname()
    {
        return $this->Dname;
    }

    public function setDname(string $text)
    {
        $this->Dname = $text;
        return $this;
    }

     public function getDlabel()
    {
        return $this->Dlabel;
    }

    public function setDlabel(string $text)
    {
        $this->Dlabel = $text;
        return $this;
    }

      public function getDprompt()
    {
        return $this->Dprompt;
    }

    public function setDprompt(string $text)
    {
        $this->Dprompt = $text;
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

    public function getDupdated()
    {
        return $this->Dupdated;
    }

    public function setDupdated( $date)
    {
        $this->Dupdated = $date;
        return $this;
    }
}

