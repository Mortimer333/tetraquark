<?php declare(strict_types=1);

namespace Tetraquark\Analyzer\JavaScript;

abstract class Validate
{
    protected static array $conntectors = [
        "+" => true, "-" => true, "/" => true, "*" => true, '%' => true, '^' => true, ">" => true,
        "<" => true, '|' => true, '&' => true, '?' => true, "." => true, "(" => true, ':' => true
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
        'export' => true, 'import' => true, 'let' => true, 'yield' => true,
        'static' => true, 'null' => true, 'true' => true, 'false' => true,
        'from' => true, 'undefined' => true, 'NaN' => true, 'Infinity' => true,
    ];

    protected static array $extendingsConsts = [
        'instanceof' => true, 'typeof' => true, 'in' => true, 'extends' => true,
        'from' => true, 'new' => true
    ];

    public static function isJSTakenKeyWord(string $word): bool
    {
        return self::$notAllowedConsts[$word] ?? false;
    }

    public static function isExtendingKeyWord(string $word): bool
    {
        return self::$extendingsConsts[$word] ?? false;
    }

    public static function isJSValidVariable(string $variable): bool
    {
        $regex = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x200C\x200D]*+$/';
        $res = preg_match($regex, $variable);
        if (!$res) {
            return false;
        }

        return !self::isJSTakenKeyWord($variable);
    }

    public static function isValidUndefined(string $undefined): bool
    {
        $undefinedEnds = ["\n" => true, ";" => true, "}" => true];
        $undefined = trim($undefined);
        return \mb_strlen($undefined) > 0 && !Content::isWhitespace($undefined) && !isset($undefinedEnds[$undefined]);
    }

    public static function isSymbol(string $letter): bool
    {
        return !preg_match('/^[a-zA-Z0-9]*$/', $letter) && !Content::isWhitespace($letter);
    }

    public static function isConnector(string $letter): bool
    {
        return self::$conntectors[$letter] ?? false;
    }

    public static function isOperator(string $letter, bool $isNextLine = false): bool
    {
        $operators = [...self::$conntectors, "{" => true,  "[" => true, "=" => true, "!" => true, ];
        if ($isNextLine) {
            $operators = [...$operators, [ ')' => true, ']' => true, '}' => true, ]];
        }
        return $operators[$letter] ?? false;
    }

    public static function isComment(int $pos, Content $content): bool
    {
        return $content->getLetter($pos) == '/' && (
            $content->getLetter($pos + 1) == '*'
            || $content->getLetter($pos + 1) == '/'
        );
    }
}
