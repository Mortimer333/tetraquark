<?php
namespace Tetraquark\Trait;

trait BlockValidateTrait
{
    protected array $special = [
        "(" => true, ")" => true, "{" => true, "}" => true, "+" => true, "-" => true, "/" => true, "*" => true,
        "=" => true, "!" => true, '[' => true, ']' => true, '%' => true, '^' => true, ":" => true, ">" => true,
        "<" => true, "," => true, ' ' => true, "\n" => true, "\r" => true, '|' => true, '&' => true, '?' => true,
        ';' => true, '.' => true
    ];

    protected array $notAllowedConsts = [
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

    protected function isValidVariable(string $variable): bool
    {
        $regex = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x200C\x200D]*+$/';
        $res = preg_match($regex, $variable);
        if (!$res) {
            return false;
        }

        return !isset($notAllowedConsts[$variable]);
    }

    protected function isWhitespace(string $letter): bool
    {
        return ctype_space($letter);
    }

    protected function isValidUndefined(string $undefined): bool
    {
        $undefinedEnds = ["\n" => true, ";" => true, "}" => true];
        $undefined = trim($undefined);
        return \mb_strlen($undefined) > 0 && !$this->isWhitespace($undefined) && !isset($undefinedEnds[$undefined]);
    }

    protected function isTemplateLiteralLandmark(string $letter, string $previousLetter, bool $inString = false): bool
    {
        return $letter === '`' && (
            $inString && $previousLetter !== '\\'
            || !$inString
        );
    }

    protected function isString(string $letter): bool
    {
        $strings = [
            '"' => true,
            "'" => true,
            '`' => true,
        ];
        return $strings[$letter] ?? false;
    }

    protected function isStringLandmark(string $letter, string $previousLetter, bool $inString = false): bool
    {
        return ($letter === '"' || $letter === "'")
            && (
                !$inString
                || $inString && $previousLetter !== '\\'
            );
    }

    protected function isSpecial(string $letter): bool
    {
        return $this->special[$letter] ?? false;
    }
}
