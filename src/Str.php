<?php declare(strict_types=1);

namespace Tetraquark;

use Content\Utf8 as Content;
use Orator\Log;

abstract class Str
{
    public static function utf8rev(string $text): string
    {
        return (new Content($text))->reverse() . '';
    }

    public static function skipBlock(string|array $needle, int $start, Content $content, null|array|string $hayStarter = null): array
    {
        if (is_string($needle)) {
            $needle = [$needle];
        }

        if (is_string($hayStarter)) {
            $hayStarter = [$hayStarter];
        }

        if (empty($needle)) {
            throw new Exception("Needle can't be empty", 400);
        }

        $hayStartes = [];
        foreach ($hayStarter ?? [] as $value) {
            $hayStartes[$value] = [
                "needle" => $value,
                "len" => mb_strlen($value),
            ];
        }

        // @POTENTIAL_PREFORMANCE_ISSUE
        $tmpNeedle = [];
        foreach ($needle as $value) {
            $tmpNeedle[$value] = [
                "needle" => $value,
                "len" => mb_strlen($value),
            ];
        }
        $needle = $tmpNeedle;
        $skip   = 0;

        for ($i = $start; $i < $content->getLength(); $i++) {
            $i = self::skip($content->getLetter($i), $i, $content);
            $letter = $content->getLetter($i);

            foreach ($hayStartes as $key => &$starter) {
                if ($letter == $key[0]) {
                    $posNeedle = $content->subStr($i, $starter['len']);
                    if ($posNeedle === $key) {
                        $i += $starter['len'] - 1;
                        $skip++;
                        continue 2;
                    }
                }
            }

            foreach ($needle as $key => &$straw) {
                if ($letter == $key[0]) {
                    $posNeedle = $content->subStr($i, $straw['len']);
                    if ($posNeedle === $key) {
                        if ($skip > 0) {
                            $skip--;
                            continue 2;
                        }

                        $i += $straw['len'] - 1;
                        return [$i + 1, $key];
                    }
                }
            }
        }

        return [$content->getLength(), null];
    }

    public static function skip(string $strLandmark, int $start, Content $content, bool $reverse = false): int
    {
        if (
            !($isTemplate = Validate::isTemplateLiteralLandmark($strLandmark, ''))
            && !Validate::isStringLandmark($strLandmark, '')
        ) {
            return $start;
        }

        if ($reverse) {
            $content = $content->reverse();
        }


        $preLetter = '';
        for ($i=$start + 1; $i < $content->getLength(); $i++) {
            $letter = $content->getLetter($i);
            if ($isTemplate) {
                if ((
                    !$reverse && $preLetter . $letter == '${'
                ) || (
                    $reverse && $preLetter . $letter == '{$'
                )) {
                    list($i) = self::skipBlock("}", $i + 1, $content, "{");
                    $letter = $content->getLetter($i);
                }
            }

            if ((
                $isTemplate
                && Validate::isTemplateLiteralLandmark($letter, $content->getLetter($i - 1) ?? '', true)
                && $letter === $strLandmark
            ) || (
                !$isTemplate
                && Validate::isStringLandmark($letter, $content->getLetter($i - 1) ?? '', true)
                && $letter === $strLandmark
            )) {
                if ($reverse) {
                    return $content->getLength() - $i - 2;
                }
                return $i + 1;
            }

            $preLetter = $letter;
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
            if (!Content::isWhitespace($letter)) {
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
            if (!Content::isWhitespace($letter)) {
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
            if (!Content::isWhitespace($letter ?? '')) {
                $letterFound = true;
            }
        }

        for ($i=$start; $i >= 0; $i--) {
            $letter = $content->getLetter($i);
            if (Content::isWhitespace($letter) || Validate::isSpecial($letter)) {
                if (Content::isWhitespace($letter) && !$startSearch && !$letterFound) {
                    $startSearch = true;
                } elseif (Validate::isSpecial($letter) && !$letterFound) {
                    $letterFound = true;
                } elseif ($letterFound && !Content::isWhitespace($word)) {
                    Log::log('Last letter: ' . $letter);
                    return [Str::utf8rev($word), $i + 1];
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

        return [Str::utf8rev($word), 0];
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
            if (!Content::isWhitespace($letter ?? '')) {
                $letterFound = true;
            }
        }
        $word = '';
        for ($i=$start; $i < $content->getLength(); $i++) {
            $letter = $content->getLetter($i);
            if (Content::isWhitespace($letter) || Validate::isSpecial($letter)) {
                if (Content::isWhitespace($letter) && !$startSearch && !$letterFound) {
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

        return [$word, $content->getLength() - 1];
    }

    public static function bool(bool $bool): string
    {
        return $bool ? 'true' : 'false';
    }

    public static function utf8ToUnicode(string $letter): string
    {
        return sprintf('U+%04X', \IntlChar::ord($letter));
    }

    public static function unicodeToUtf8(string $letter): string
    {
        return html_entity_decode(preg_replace("/U\+([0-9A-F]{4})/", "&#x\\1;", $letter), ENT_NOQUOTES, 'UTF-8');
    }
}
