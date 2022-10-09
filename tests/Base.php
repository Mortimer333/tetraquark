<?php declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

abstract class Base extends TestCase
{
    public function setUp(): void
    {
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            throw new \RuntimeException($errstr . " on line " . $errline . " in file " . $errfile);
        });
    }

    public function tearDown():void {
        restore_error_handler();
    }

    protected function log(string $string): self
    {
        fwrite(STDERR, $string);
        return $this;
    }
}
