<?php declare(strict_types=1);

namespace Tests\Integration;

use Tetraquark\Analyzer\BaseAnalyzerAbstract;

class TestAnalyzer extends BaseAnalyzerAbstract {
    public static array $schemat = [];
    public static function getSchema(array $settings = []): array
    {
        return self::$schemat;
    }
}
