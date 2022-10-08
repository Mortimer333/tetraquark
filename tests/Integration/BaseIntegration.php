<?php declare(strict_types=1);

namespace Tests\Integration;

use Tests\Base;

abstract class BaseIntegration extends Base
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getSchemat(string $path): array
    {
        $path = __DIR__ . '/test-schemat/' . rtrim(ltrim($path, .'/'), ".php") . '.php';
        if (!is_file($path)) {
            throw new \Exception(sprintf('Schemat %s doesn\'t exist', $path));
        }

        return require $path;
    }

    protected function getScript(string $path)
    {
        $path = __DIR__ . '/test-script/' . ltrim($path, .'/');
        if (!is_file($path)) {
            throw new \Exception(sprintf('Script %s doesn\'t exist', $path));
        }

        return file_get_contents($path);
    }
}
