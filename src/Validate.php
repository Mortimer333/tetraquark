<?php declare(strict_types=1);

namespace Tetraquark;

class Validate
{
    protected static array $special = [
        "(" => true, ")" => true, "{" => true, "}" => true, "+" => true, "-" => true, "/" => true, "*" => true,
        "=" => true, "!" => true, '[' => true, ']' => true, '%' => true, '^' => true, ":" => true, ">" => true,
        "<" => true, "," => true, ' ' => true, "\n" => true, "\r" => true, '|' => true, '&' => true, '?' => true,
        ';' => true, '.' => true
    ];

    protected static array $operators = [
        "+" => true, "-" => true, "/" => true, "*" => true, "=" => true, "!" => true, '%' => true, '^' => true,
        ">" => true, "<" => true, '|' => true, '&' => true, '?' => true, "." => true
    ];

    protected static array $notAllowedConsts = [
        'break' => true, 'do' => true, 'instanceof' => true,
        'typeof' => true, 'case' => true, 'else' => true, 'new' => true,
        'var' => true, 'catch' => true, 'finally' => true, 'return' => true,
        'void' => true, 'continue' => true, 'for' => true, 'switch' => true,
        'while' => true, 'debugger' => true, 'function' => true, 'this' => true,
        'with' => true, 'default' => true, 'if' => true, 'throw' => true,
        'delete' => true, 'in' => true, 'try' => true, 'class' => true,
        'enum' => true, 'extends' => true, 'super' => true, 'const' => true,
        'export' => true, 'import' => true, 'implements' => true, 'let' => true,
        'private' => true, 'public' => true, 'yield' => true, 'interface' => true,
        'package' => true, 'protected' => true, 'static' => true, 'null' => true,
        'true' => true, 'false' => true
    ];

    public static function getSpecial(): array
    {
        return self::$special;
    }

    public static function isValidVariable(string $variable): bool
    {
        $regex = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x200C\x200D]*+$/';
        $res = preg_match($regex, $variable);
        if (!$res) {
            return false;
        }

        return !isset($notAllowedConsts[$variable]);
    }

    public static function isWhitespace(string $letter): bool
    {
        return ctype_space($letter);
    }

    public static function isValidUndefined(string $undefined): bool
    {
        $undefinedEnds = ["\n" => true, ";" => true, "}" => true];
        $undefined = trim($undefined);
        return \mb_strlen($undefined) > 0 && !self::isWhitespace($undefined) && !isset($undefinedEnds[$undefined]);
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

    public static function isStringLandmark(string $letter, string $previousLetter, bool $inString = false): bool
    {
        return ($letter === '"' || $letter === "'")
            && (
                !$inString
                || $inString && $previousLetter !== '\\'
            );
    }

    public static function isSpecial(string $letter): bool
    {
        return self::$special[$letter] ?? false;
    }

    public static function isSymbol(string $letter): bool
    {
        return !preg_match('/^[a-zA-Z0-9]*$/', $letter) && !self::isWhitespace($letter);
    }

    public static function isOperator(string $letter): bool
    {
        return self::$operators[$letter] ?? false;
    }

    public static function isComment(int $pos, Content $content): bool
    {
        return $content->getLetter($pos) == '/' && (
            $content->getLetter($pos + 1) == '*'
            || $content->getLetter($pos + 1) == '/'
        );
    }
}
