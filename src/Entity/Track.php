<?php

namespace App\Entity;

use App\Repository\TrackRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TrackRepository::class)
 */
class Track
{

 /**
     * @ORM\Id
     * @ORM\Column(name="trackid",type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $TrackId;

     /**
     * @ORM\Column(name="fixerid",type="integer")
     */
    private $FixerId;

    /**
     * @ORM\Column(name="repairid",type="integer")
     */
    private $RepairId;

    /**
     * @ORM\Column(name="notes",type="text", length=50)
     */
    private $Notes;


    /**
     * @ORM\Column(name="lastvisit",type="datetime")
     */
    private $Lastvisit;

        /**
     * @ORM\Column(name="follow",type="boolean")
     */
    private $Follow;

      public function getTrackId()
    {
        return $this->TrackId;
    }

    public function setTrackId($number)
    {
        $this->TrackId= $number;
        return $this;
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

     public function getRepairId()
    {
        return $this->RepairId;
    }

    public function setRepairId($number)
    {
        $this->RepairId= $number;
        return $this;
    }

    public function getNotes()
    {
        return $this->Notes;
    }

    public function setNotes(string $Name)
    {
        $this->Notes = $Name;
        return $this;
    }

    public function getFollow()
    {
        return $this->Follow;
    }

    public function setFollow($bool)
    {
        $this->Follow = $bool;
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

     public function setUpdated($date)
    {
        $this->Lastvisit = $date;
        return $this;
    }

}

