<?php declare(strict_types=1);

namespace Tetraquark;

/**
 * MultiByte string Polyfill
 */
class Str
{
    public static function rev(string $text): string
    {
        $str = iconv('UTF-8','windows-1251',$text);
        $string = strrev($str);
        $str = iconv('windows-1251', 'UTF-8', $string);
        return $str;
    }

    // https://stackoverflow.com/a/14366023/11495586
    public static function get(string $string, int $pointer, int &$nextLetter): string|bool
    {
        if (!isset($string[$pointer])) {
            return false;
        }

        $char = ord($string[$pointer]);

        if ($char < 128) {
            $nextLetter = $pointer + 1;
            return $string[$pointer];
        }

        if ($char < 224) {
            $bytes = 2;
        } elseif ($char < 240) {
            $bytes = 3;
        } else {
            $bytes = 4;
        }
        $str = substr($string, $pointer, $bytes);
        $nextLetter = $pointer + $bytes;
        return $str;
    }

    public static function iterate(string $content, int $i, array $args, callable $func)
    {
        $nextLetter = $i;
        while ($letter = Str::get($content, $i, $nextLetter)) {
            $res = $func($letter, $i, ...$args);
            $i = $nextLetter;
        }
        return $res;
    }
}
