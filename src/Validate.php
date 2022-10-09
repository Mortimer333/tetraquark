<?php declare(strict_types=1);

namespace Tetraquark;

use Content\Utf8 as Content;

abstract class Validate
{
    protected static array $special = [
        "(" => true, ")" => true, "{" => true, "}" => true, "+" => true, "-" => true, "/" => true, "*" => true,
        "=" => true, "!" => true, '[' => true, ']' => true, '%' => true, '^' => true, ":" => true, ">" => true,
        "<" => true, "," => true, ' ' => true, "\n" => true, "\r" => true, '|' => true, '&' => true, '?' => true,
        ';' => true, '.' => true
    ];

    /**
     * @codeCoverageIgnore
     */
    public static function getSpecial(): array
    {
        return self::$special;
    }

    /**
     * @codeCoverageIgnore
     */
    public static function isSpecial(string $letter): bool
    {
        return self::$special[$letter] ?? false;
    }

    public static function isTemplateLiteralLandmark(string $letter, string $previousLetter, bool $inString = false): bool
    {
        return $letter === '`' && (
            $inString && $previousLetter !== '\\'
            || !$inString
        );
    }

    public static function isStringChar(string $letter): bool
    {
        $strings = [
            '"' => true,
            "'" => true,
            '`' => true,
        ];
        return $strings[$letter] ?? false;
    }

    public static function isStringLandmark(string $letter, string $previousLetter = '', bool $inString = false): bool
    {
        return ($letter === '"' || $letter === "'" || $letter === "`")
            && (
                !$inString
                || ($inString && $previousLetter !== '\\')
            );
    }
}
