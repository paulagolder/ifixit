<?php

// src/Service/MyLibrary.php

namespace App\Service;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use App\Entity\Image;
use App\Entity\Person;
use App\Repository\PersonRepository;

class MyLibrary
{

    private  $newimagespath="";
    private  $oldimagespath="";

    public function __construct(string $newimagespath,string $oldimagespath)
    {
        $this->newimagespath = $newimagespath;
        $this->oldimagespath = $oldimagespath;

    }


    static public function selectText($text_ar,$attribute,$language)
    {
        $lanu = strtoupper($language);
        if( array_key_exists ($attribute,$text_ar ))
        {
            if( array_key_exists ($lanu,$text_ar[$attribute] )) return $text_ar[$attribute][$lanu]["comment"];
            if(array_key_exists ("FR",$text_ar[$attribute] ) )return $text_ar[$attribute]["FR"]["comment"] ;
            if(array_key_exists ("fr",$text_ar[$attribute] ) )return $text_ar[$attribute]["fr"]["comment"] ;
            if(array_key_exists ("EN",$text_ar[$attribute] )) return $text_ar[$attribute]["EN"]["comment"] ;
            if(array_key_exists ("en",$text_ar[$attribute] )) return $text_ar[$attribute]["en"]["comment"] ;
        }
        return null;
    }

    private function setText($text_target,$text_ar,$attribute)
    {

        if(  array_key_exists ($attribute , $text_ar))
        {
            $textatt =$text_ar[$attribute];
            if(array_key_exists( "FR" , $textatt)) $text_target["FR"]= $textatt["FR"] ;
            if(array_key_exists( "EN" , $textatt)) $text_target["EN"]= $textatt["EN"] ;
        }
        return $text_target;
    }


    static public function getText($text_ar,$attribute,$language)
    {
        if( array_key_exists ( $language , $text_ar[$attribute] ))return $text_ar[$attribute][$language] ;
        //if($text_ar[$attribute][$language] ) return $text_ar[$attribute][$language] ;
        if( array_key_exists ( "FR" , $text_ar[$attribute] )) return $text_ar[$attribute]["FR"] ;
        if( array_key_exists ( "EN" , $text_ar[$attribute] )) return $text_ar[$attribute]["EN"] ;
        return "No text found";
    }

    static public function getTexts($text_ar,$attribute)
    {
        if($text_ar[$attribute]) return $text_ar[$attribute] ;
        return "No text found";
    }



    public function xgetMess()
    {

        return "happy messagfe";
    }


    public function getCookieLang()
    {

        $request = new Request();
        $cookies = $request->cookies;
        $lang = $_COOKIE["symfony_lang"];
        if($lang) return $lang;
        else return '' ;
    }


    public function setLang($lang)
    {
        $this->lang = $lang;
        $path ="localhost";
        $cookie = new Cookie
        (
            'symfony_lang',    // Cookie name.
            $lang,    // Cookie value.
            time() + ( 24 * 60 * 60),  // Expires 1 day .
            $path
            );
            $res = new Response();
            $res->headers->setCookie( $cookie );
            $res->send();
    }


    public function toLocale($lang)
    {
        $toLocale= ['FR'=>'fr_FR','EN'=>'en_EN','fr'=>'fr_FR','en'=>'en_EN'];

        return $toLocale[$lang];
    }

    public function xgetLang()
    {
        $request = new Request();
        $locale =    $request->getLocale();

        echo( "getlang 1 ".$locale ." ");
        if($locale != NULL )
        {
            $k = strpos($locale, "-");
            if ($k<1)
                $lang =  strtolower($locale);
            else
            {
                $lang= substr($locale,0,$k);
                $lang= strtolower($lang);
            }
        }
        else
        {
            $lang = $this->getCookieLang();
            if($lang == NULL )
            {
                $lang= "FR";
            }
            $request->setLocale($lang);
        }

        echo( "getlang 2 ".$lang." " );
        return $lang;
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
