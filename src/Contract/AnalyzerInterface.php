<?php declare(strict_types=1);

namespace Tetraquark\Contract;

interface AnalyzerInterface
{
    public static function getSchema(array $settings = []): array;
    public static function getName(): string;
}
