<?php

namespace App\Entity;

use App\Repository\DialogreplyRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DialogreplyRepository::class)
 */
class Dialogreply
{
     /**
     * @ORM\Id
     * @ORM\Column(name="replyid",type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $ReplyId;


       /**
     * @ORM\Column(name="repairid",type="integer")
     */
    private $RepairId;

    /**
     * @ORM\Column(name="dialogname",type="string", length=20)
     */
    private $Dialogname;

        /**
     * @ORM\Column(name="dialogreply",type="string", length=200)
     */
    private $Reply;


            /**
     * @ORM\Column(name="updated",type="datetime")
     */
    private $Updated;

    public function getReplyId()
    {
        return $this->ReplyId;
    }

    public function setReplyId($number)
    {
        $this->ReplyId = $number;
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



    public function getDialogname()
    {
        return $this->Dialogname;
    }

    public function setDialogname(string $Name)
    {
        $this->Dialogname = $Name;
        return $this;
    }

    public function getDialogreply()
    {
        return $this->Reply;
    }

    public function setDialogreply(string $Name)
    {
        $this->Reply = $Name;
        return $this;
    }

       public function getUpdated()
    {
        return $this->Updated;
    }

    public function setUpdated(string $Name)
    {
        $this->Updated = $Name;
        return $this;
    }

}

