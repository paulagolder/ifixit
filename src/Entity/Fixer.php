<?php

namespace App\Entity;

use App\Repository\FixerRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FixerRepository::class)
 */
class Fixer
{
     /**
     * @ORM\Id
     * @ORM\Column(name="fixerid",type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $FixerId;

    /**
     * @ORM\Column(name="nickname",type="string", length=50)
     */
    private $Nickname;


    /**
     * @ORM\Column(name="fullname",type="string", length=50)
     */
    private $Fullname;

    /**
     * @ORM\Column(name="email",type="text", length=50)
     */
    private $Email;

    /**
     * @ORM\Column(name="lastvisit",type="datetime")
     */
    private $Lastvisit;

    public function getFixerId()
    {
        return $this->FixerId;
    }

    public function setFixerId($number)
    {
        $this->FixerId= $number;
        return $this;
    }

    public function getNickname()
    {
        return $this->Nickname;
    }

    public function setNickname(string $Name)
    {
        $this->Nickname = $Name;
        return $this;
    }


      public function getFullname()
    {
        return $this->Fullname;
    }

    public function setFullname(string $Name)
    {
        $this->Fullname = $Name;
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


    public function getLastvisit()
    {
        return $this->Lastvisit;
    }

    public function setLastvisit($date)
    {
        $this->Lastvisit = $date;
        return $this;
    }

}

