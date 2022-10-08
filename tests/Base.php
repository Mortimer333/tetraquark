<?php declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

abstract class Base extends TestCase
{
    public function setUp(): void
    {
    }

    protected function log(string $string): self
    {
        fwrite(STDERR, $string);
        return $this;
    }
}
