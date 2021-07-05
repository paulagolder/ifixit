<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     *  string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(name="lastvisit",type="datetime")
     */
    private $lastlogin;


    /**
     * @ORM\Column(name="skills",type="json", length=100)
     */
    private $skills;

    /**
     * @ORM\Column(name="membership", type="string", length = 20)
     */
    private $membership;

    /**
     * @ORM\Column(name="apitoken", type="string", length = 255)
     */
    private $apiToken;

    /**
     * @ORM\Column(name="fixerkey", type="string", length = 255)
     */
    private $fixerkey;


    public function getId(): ?int
    {
        return $this->id;
    }


    public function geUserId(): ?int
    {
      return $this->id;
    }

    /**
     * @ORM\Column(name="nickname",type="string", length=50,  unique=true)
     */
    private $nickname;


    /**
     * @ORM\Column(name="fullname",type="string", length=50, unique=true)
     */
    private $fullname;




    private $plainPassword;


    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }


    public function getUsername(): string
    {
        return (string) $this->nickname;
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


    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole($role): self
    {
      $this->roles[] = $role;

      return $this;
    }


    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): string
    {
      return $this->plainPassword;
    }

    public function setPlainPassword(string $password): self
    {
      $this->plainPassword = $password;

      return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     */
    public function getSalt(): ?string
    {
        return null;
    }


    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getSkills(): array
    {
      $skills = $this->skills;
      // guarantee every user at least has ROLE_USER
      $skills[] = 'Fixer';

      return array_unique($skills);
    }

    public function setSkills(array $skills): self
    {
      $this->skills = $skills;

      return $this;
    }

    public function getSkillstr()
    {
      $skills = $this->skills;
      return json_encode($skills);
    }

    public function setSkillstr(string $text): self
    {
      $this->skills = json_decode( $text);

      return $this;
    }

    public function getRolesstr()
     {
       $roles = $this->roles;
       return json_encode($roles);
     }

     public function setRolesstr(string $text): self
     {

       $this->roles= json_decode( $text);

     return $this;
     }




    public function getFixerkey(): string
    {
      return $this->fixerkey;
    }

    public function setFixerkey(string $token): self
    {
      $this->fixerkey= $token;

      return $this;
    }

    public function getNickname()
     {
       return $this->nickname;
     }

     public function setNickname(string $Name)
     {
       $this->nickname = $Name;
       return $this;
     }


     public function getFullname()
     {
       return $this->fullname;
     }

     public function setFullname(string $Name)
     {
       $this->fullname = $Name;
       return $this;
     }

}
