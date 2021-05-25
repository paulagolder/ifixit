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

    public function getRepairId()
    {
        return $this->RepairId;
    }

    public function setRepairId($number)
    {
        $this->Name = $number;
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


}

