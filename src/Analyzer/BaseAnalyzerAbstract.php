<?php declare(strict_types=1);

namespace Tetraquark\Analyzer;

use Tetraquark\Contract\AnalyzerInterface;

/**
 * @codeCoverageIgnore
 */
abstract class BaseAnalyzerAbstract implements AnalyzerInterface
{
    public static function getName(): string
    {
        return static::class;
    }

    public static function getSchema(array $settings = []): array
    {
        return [
            "comments" => static::getCommentsMap(),
            "prepare" => [
                "content" => static::getPrepareContent(),
                "missed" => static::getPrepareMissed(),
            ],
            "shared" => [
                "ends" => static::getSharedEnds(),
            ],
            "remove" => [
                "comments" => static::getRemoveComments(),
                "additional" => static::getRemoveAdditional(),
            ],
            "instructions" => static::getInstruction(),
            "methods" => static::getMethods(),
        ];
    }

    public static function getRemoveAdditional(array $settings = []): \Closure|bool
    {
        return false;
    }

    public static function getRemoveComments(array $settings = []): bool
    {
        return false;
    }

    public static function getCommentsMap(array $settings = []): array
    {
        return [];
    }

    public static function getPrepareMissed(array $settings = []): ?\Closure
    {
        return null;
    }

    public static function getPrepareContent(array $settings = []): ?\Closure
    {
        return null;
    }

    public static function getSharedEnds(array $settings = []): array
    {
        return [];
    }

    public static function getInstruction(array $settings = []): array
    {
        return [];
    }

    public static function getMethods(array $settings = []): array
    {
        return [];
    }
}
