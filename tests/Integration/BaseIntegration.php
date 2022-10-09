<?php declare(strict_types=1);

namespace Tests\Integration;

use Tests\Base;
use Tetraquark\Reader;

abstract class BaseIntegration extends Base
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getSchematPath(string $path): string
    {
        $path = __DIR__ . '/schemat/' . rtrim(ltrim($path, '/'), ".php") . '.php';
        if (!is_file($path)) {
            throw new \Exception(sprintf('Schemat %s doesn\'t exist', $path));
        }

        return $path;
    }

    protected function getScriptPath(string $path)
    {
        $path = __DIR__ . '/script/' . ltrim($path, '/');
        if (!is_file($path)) {
            throw new \Exception(sprintf('Script %s doesn\'t exist', $path));
        }

        return $path;
    }

    protected function getCompiled(string $path): array
    {
        $path = __DIR__ . '/schemat-compiled/' . ltrim($path, '/') . '.json';
        if (!is_file($path)) {
            throw new \Exception(sprintf('Compiled schemat %s doesn\'t exist', $path));
        }

        $json = json_decode(file_get_contents($path), true);
        if (is_null($json ) || (!$json && !empty($json))) {
            throw new \Exception(sprintf('Compiled schemat %s is malformed', $path));
        }

        return $json;
    }

    protected function getAnalysis(string $path): array
    {
        $path = __DIR__ . '/analysis/' . ltrim($path, '/') . '.json';
        if (!is_file($path)) {
            throw new \Exception(sprintf('Analysis %s doesn\'t exist', $path));
        }

        $json = json_decode(file_get_contents($path), true);
        if (is_null($json ) || (!$json && !empty($json))) {
            throw new \Exception(sprintf('Analysis %s is malformed', $path));
        }

        return $json;
    }

    public function getReaderWithTestAnalyzer(string|array $schemat): Reader
    {
        if (is_string($schemat)) {
            TestAnalyzer::$schemat = require BaseIntegration::getSchematPath($schemat);
        } else {
            TestAnalyzer::$schemat = $schemat;
        }
        return new Reader(TestAnalyzer::class, cache: false);
    }
}
