<?php declare(strict_types=1);

namespace Tetraquark\Analyzer;

use Orator\Log;
use Content\Utf8 as Content;
use Tetraquark\{Str, Validate};
use Tetraquark\Analyzer\JavaScript\{Instruction, Validate as JsValidate, Methods};
use Tetraquark\Analyzer\JavaScript\Util\Helper;
use Tetraquark\Model\CustomMethodEssentialsModel;

abstract class JavaScriptAnalyzerAbstract extends BaseAnalyzerAbstract
{
    public static function getCommentsMap(array $settings = []): array
    {
        return [
            "/" => [
                "/" => "\n",
                "*" => "*/"
            ],
        ];
    }

    public static function getPrepareMissed(array $settings = []): \Closure
    {
        return fn(string $missed) => trim(trim(trim($missed), ';'));
    }

    public static function getPrepareContent(array $settings = []): \Closure
    {
        return fn(Content $content) => $content->prependArrayContent([' ']);
    }

    public static function getSharedEnds(array $settings = []): array
    {
        return [
            "\n" => true,
            ";" => true,
            "}" => true,
            "," => true,
            ")" => true,
        ];
    }

    public static function getInstruction(array $settings = []): array
    {
        return Instruction::get();
    }

    public static function getMethods(array $settings = []): array
    {
        return Methods::get();
    }
}
