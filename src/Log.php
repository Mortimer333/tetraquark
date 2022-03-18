<?php declare(strict_types=1);

namespace Tetraquark;

class Log
{
    static protected int  $indent = 0;
    static protected int  $verbose = 0;
    static protected int  $maxVerbose = 0;
    static protected bool $addClass   = false;

    function __construct()
    {
    }

    static public function log(string $output, ?int $verbose = null): void
    {
        if (is_null($verbose)) {
            $verbose = self::$verbose;
        }

        if ($verbose <= self::$maxVerbose) {
            $message = str_repeat("  ", self::$indent) . $output . PHP_EOL;
            if (self::$addClass) {
                $message = debug_backtrace()[1]['class'] . '| ' . $message;
            }
            echo $message;
        }
    }

    static public function getIndent(): int
    {
        return self::$indent;
    }

    static public function setIndent(int $indent): void
    {
        self::$indent = $indent;
    }

    static public function increaseIndent(): void
    {
        self::$indent++;
    }

    static public function decreaseIndent(): void
    {
        self::$indent--;
    }

    static public function setVerboseLevel(int $level): void
    {
        self::$verbose = $level;
    }
}
