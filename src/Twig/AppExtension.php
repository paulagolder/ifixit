<?php

// src/Twig/AppExtension.php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class AppExtension extends AbstractExtension
{
    private $session;

     public function __construct(SessionInterface $session)
     {
         $this->session = $session;
     }

    public function getFilters()
    {
        return [
            new TwigFilter('jsondecode', [$this,'jsonDecode']),
            new TwigFilter('jsondecodearray', [$this,'jsonDecodeArray']),
            new TwigFilter('getelement', [$this,'getElement']),
            new TwigFilter('cast_to_array', array($this, 'objectFilter')),
        ];
    }


    public function jsonDecodeArray($string)
    {
        return json_decode($string, true);
    }



    public function jsonDecode($string)
    {
        return json_decode($string);
    }

    public function getElement($arry,$string)
    {
        if(is_array($arry))
        {
        if($string)
        {
        if( array_key_exists($string, $arry))
        {

           return $arry[$string];
        }
        else
        {
           if( array_key_exists(0, $arry))
               return $arry[0];
                  else return null;
        }
        }
        }
        else  return $arry;
    }

    public function objectFilter($stdClassObject)
    {
         $response = (array)$stdClassObject;
         return $response;
     }

 }
