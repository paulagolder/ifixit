<?php

// src/Service/MyLibrary.php

namespace App\Service;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use App\Entity\Image;



class MyLibrary
{



    public function __construct()
    {


    }




    public function getCookieLang()
    {

        $request = new Request();
        $cookies = $request->cookies;
        $lang = $_COOKIE["symfony_lang"];
        if($lang) return $lang;
        else return '' ;
    }



    static public function formatDate($date, $lang)
    {
        setlocale(LC_TIME, "");
        if($lang =="EN" | $lang=="en" )
            setlocale (LC_TIME, 'en_EN.utf-8');
        else
            setlocale (LC_TIME, 'fr_FR.utf-8','fr_FR');
        if(substr($date,4,4)=="0000")
        {
            return substr($date,0,4);
        }
        else if(substr($date,6,2)=="00")
        {
            $ddate = substr($date,0,6)."01";
              $ddate = substr($date,0,4)."/".substr($date,4,2)."/30";
            $dfdate = strtotime($ddate);
              return strftime('%B %G', $dfdate);
        }
        if (($timestamp = strtotime($date)) === false)
        {
            return " ".$date;
        } else
        {
            $dfdate = strtotime($date);
            return strftime('%A %d %B %G', $dfdate);
        }

    }

    public function setFullpath($image)
    { if($image)
        {
            if ($image->isTemp())
            {
                $image->setFullpath ($this->newimagespath.$image->getPath());
            }
            else
            {
                $image->setFullpath ($this->oldimagespath.$image->getPath());
            }
        }
    }

    public function setCookieFilter($key,$value)
    {
        $cookie = new Cookie
        (
            'FFLSAS_'.$key,    // Cookie name.
            $value,    // Cookie value.
            time() + ( 24 * 60 * 60)  // Expires 1 day .
            );
            $res = new Response();
            $res->headers->setCookie( $cookie );
            $res->send();
    }

    public function getCookieFilter($key)
    {
        $request = Request::createFromGlobals();
        $cookies = $request->cookies;
        $value="";
        if ($cookies->has("FFLSAS_".$key))
        {
            $value = $_COOKIE["FFLSAS_".$key];
        }
        if($value) return $value;
        else return '' ;
    }


    public function clearCookieFilter($key)
    {
        $res = new Response();
        $res->headers->clearCookie("FFLSAS_".$key);
        $res->send();
    }







}
