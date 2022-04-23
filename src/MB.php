<?php
namespace Tetraquark;

/**
 * MultiByte string Polyfill
 */
class MB
{
    public static function strrev($text){
        $str = iconv('UTF-8','windows-1251',$text);
        $string = strrev($str);
        $str = iconv('windows-1251', 'UTF-8', $string);
        return $str;
    }
}
