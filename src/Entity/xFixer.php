<?php

namespace App\Entity;

use App\Repository\FixerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=FixerRepository::class)
 */
class Fixer implements UserInterface, \Serializable
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
   * @ORM\Column(type="string", length=64)
   */
  private $password;

    /**
     * @ORM\Column(name="lastvisit",type="datetime")
     */
    private $Lastvisit;

      /**
   * @ORM\Column(type="string", length=40)
   */
  private $rolestr;

      /**
   * @ORM\Column(name="skills",type="string", length=40)
   */
  private $Skills;

  /**
   * @ORM\Column(name="membership", type="string", length = 20)
   */
  private $membership;

  private $plainPassword;
  private $newregistrationcode;
  public $link;

 public function getPassword()
   {
     return $this->password;
   }

    public function getRoles()
   {
     $roles = explode(";", $this->rolestr);
   foreach($roles as $index => $role)
   {
      $roles[$index] = trim($role);
   }
   return $roles;
   }

   public function getRolestr()
   {
     return $this->rolestr;
   }

   public function setRolestr($rolestr)
   {
     $this->rolestr = $rolestr;
   }

   public function setRoles($roles)
   {
     $this->rolestr="";
     foreach($roles as $index => $role)
   {
     $this->rolestr .= ";".trim($role);
   }

   }



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

    public function getPlainPassword()
   {
     return $this->plainPassword;
   }

  public function getMembership()
   {
     return $this->membership;
   }

   public function setMembership($text)
   {
     return $this->membership=$text;
   }

     public function getSkills()
   {
     return $this->Skills;
   }

   public function setSkills($text)
   {
     return $this->Skills=$text;
   }

    public function setPlainPassword($text)
   {
      $this->plainPassword = $text;
   }




   public function setNewregistrationcode($codeno)
   {
     $this->newregistrationcode = $codeno;
   }

   public function setPassword($password)
   {
     $this->password = $password;
   }


    public function getUsername()
   {
     return $this->Nickname;
   }

   public function getLabel()
   {
     return $this->username;
   }






   public function getSalt()
   {
     // you *may* need a real salt depending on your encoder
     // see section on salt below
     return null;
   }



   /**
    * @param $salt
    * @return Account
    */
   public function setSalt($salt)
   {
     $this->salt = $salt;

     return $this;
   }



   public function getNewregistrationcode()
   {
     return $this->newregistrationcode;
   }









   public function getLastlogin(): ?\DateTime
   {
     return $this->lastlogin;
   }

   public function setLastlogin(?\DateTime $dt): self
   {
     $this->lastlogin = $dt;
     return $this;
   }


   public function setUsername($username)
   {
     $this->username = $username;
   }





   public function eraseCredentials()
   {

     # $this->setPlainPassword(null);
   }

    /** @see \Serializable::serialize() */
   public function serialize()
   {
     return serialize(array(
       $this->id,
       $this->username,
       $this->password,
       # $this->salt,
   ));
   }

   /** @see \Serializable::unserialize() */
   public function unserialize($serialized)
   {
     list (
       $this->id,
       $this->username,
       $this->password,
       # $this->salt,
   ) = unserialize($serialized, ['allowed_classes' => false]);
   }

}

