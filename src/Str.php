<?php declare(strict_types=1);

namespace Tetraquark;

/**
 * MultiByte string Polyfill
 */
abstract class Str
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
        $res = null;
        while (($letter = Str::get($content, $i, $nextLetter)) !== false) {
            $res = $func($letter, $i, ...$args);
            $i = $nextLetter;
        }
        return $res;
    }

    public static function getFile(string $path)
    {
        if (!\is_file($path)) {
            throw new Exception('Passed file `' . htmlentities($path) . '` not found, did you provide absolute path?', 404);
        }

        return \file_get_contents($path);
    }

    public static function skip(string $strLandmark, int $start, Content $content, bool $reverse = false): int
    {
        if (
            !($isTemplate = Validate::isTemplateLiteralLandmark($strLandmark, ''))
            && !Validate::isStringLandmark($strLandmark, '')
        ) {
            return $start;
        }

        $modifier = (((int)!$reverse) * 2) - 1;
        for ($i=$start + 1; (!$reverse && $i < $content->getLength()) || ($reverse && $i >= 0); $i += $modifier) {
            $letter = $content->getLetter($i);
            if (
                $isTemplate
                && Validate::isTemplateLiteralLandmark($letter, $content->getLetter($i - 1) ?? '', true)
                && $letter === $strLandmark
            ) {
                return $i + $modifier;
            } elseif (
                !$isTemplate
                && Validate::isStringLandmark($letter, $content->getLetter($i - 1) ?? '', true)
                && $letter === $strLandmark
            ) {
                return $i + $modifier;
            }
        }
        return $i;
    }

    /**
     * Change variable name from scnake case and camel case to pascal case
     * @param  string $name Variable name
     * @return string
     */
    public static function pascalize(string $name): string
    {
        $nameAr = explode('_', $name);
        $camelized = '';
        foreach ($nameAr as $chunk) {
            $camelized .= ucfirst($chunk);
        }
        return $camelized;
    }

    /**
     * Gets the next closest non-whitespace letter
     * @param  int     $start
     * @param  Content $content
     * @return array            First item is found letter, next is letters position
     */
    public static function getNextLetter(int $start, Content $content): array
    {
        for ($i=$start; $i < $content->getLength(); $i++) {
            $letter = $content->getLetter($i);
            if (!Validate::isWhitespace($letter)) {
                return [$letter, $i];
            }
        }

        return ['', $i - 1];
    }

    /**
     * Gets the previous closest non-whitespace letter
     * @param  int     $start
     * @param  Content $content
     * @return array            First item is found letter, next is letters position
     */
    public static function getPreviousLetter(int $start, Content $content): array
    {
        for ($i=$start; $i >= 0; $i--) {
            $letter = $content->getLetter($i);
            if (!Validate::isWhitespace($letter)) {
                return [$letter, $i];
            }
        }

        return ['', 0];
    }

    /**
     * Gets the previous closest word.
     * Provides words start position and it recognizes special characters as word separators.
     * @param  int     $start
     * @param  Content $content
     * @return array            First item is found word, next is words start position
     */
    public static function getPreviousWord(int $start, Content $content, bool $startSearch = false): array
    {
        $letterFound = false;
        $word = '';
        if ($startSearch) {
            $letter = $content->getLetter($start);
            if (!Validate::isWhitespace($letter ?? '')) {
                $letterFound = true;
            }
        }
        
        for ($i=$start; $i >= 0; $i--) {
            $letter = $content->getLetter($i);
            if (Validate::isWhitespace($letter) || Validate::isSpecial($letter)) {
                if (Validate::isWhitespace($letter) && !$startSearch && !$letterFound) {
                    $startSearch = true;
                } elseif (Validate::isSpecial($letter) && !$letterFound) {
                    $letterFound = true;
                } elseif ($letterFound && !Validate::isWhitespace($word)) {
                    return [Str::rev($word), $i];
                }
                continue;
            }

            if ($letterFound) {
                $word .= $letter;
            }

            if ($startSearch && !$letterFound) {
                $word .= $letter;
                $letterFound = true;
                continue;
            }
        }

        return [Str::rev($word), 0];
    }

    /**
     * Gets the next closest word.
     * Provides words end position and it recognizes special characters as word separators.
     * @param  int     $start
     * @param  Content $content
     * @return array            First item is found word, next is words end position
     */
    public static function getNextWord(int $start, Content $content, bool $startSearch = false): array
    {
        $letterFound = false;
        if ($startSearch) {
            $letter = $content->getLetter($start);
            if (!Validate::isWhitespace($letter ?? '')) {
                $letterFound = true;
            }
        }
        $word = '';
        for ($i=$start; $i < $content->getLength(); $i++) {
            $letter = $content->getLetter($i);
            if (Validate::isWhitespace($letter) || Validate::isSpecial($letter)) {
                if (Validate::isWhitespace($letter) && !$startSearch && !$letterFound) {
                    $startSearch = true;
                } elseif (Validate::isSpecial($letter) && !$letterFound) {
                    $letterFound = true;
                } elseif ($letterFound) {
                    return [$word, $i - 1];
                }
                continue;
            }

            if ($letterFound) {
                $word .= $letter;
            }

            if ($startSearch && !$letterFound) {
                $word .= $letter;
                $letterFound = true;
                continue;
            }
        }

        return [$word, $content->getLength()];
    }
}
