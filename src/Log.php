<?php declare(strict_types=1);

namespace Tetraquark;

abstract class Log
{
    static protected int     $indent        = 0;
    static protected int     $verbose       = 0;
    static protected int     $maxVerbose    = 0;
    static protected int     $classLimit    = 50;
    static protected int     $functionLimit = 20;
    static protected array   $timeStart     = [];
    static protected bool    $addClass      = false;
    static protected bool    $addFunction   = false;
    static protected string  $oldFunction   = '';
    
    static public function timerStart(): void
    {
        self::$timeStart[] = microtime();
    }

    static public function timerEnd(): void
    {
        $timeEnd = self::getMilliseconds(microtime());

        if (empty(self::$timeStart)) {
            throw new Exception('Time start is empty', 400);
        }

        $timeStart = self::getMilliseconds(self::$timeStart[\sizeof(self::$timeStart) - 1]);
        array_pop(self::$timeStart);
        $time = $timeEnd - $timeStart;
        self::log('Duration: ' . ($time/1000) . 's', null, 1);
    }

    static private function getMilliseconds(string $microtime) {
        $mt = explode(' ', $microtime);
        return ((int)$mt[1]) * 1000 + ((int)round($mt[0] * 1000));
    }

    static public function time(): void
    {
        list($usec, $sec) = explode(" ", microtime());
        self::log(date('H:i:s:') . round($usec * 1000), 0 , 1);
    }

    static public function log(array|string|int|float $output, ?int $verbose = null, int $traceLvl = 0, bool $replaceNewLine = true): void
    {
        if (is_array($output)) {
            $output = json_encode($output, JSON_PRETTY_PRINT);
        }
        $verbose = $verbose ?? self::$verbose;
        $indentStr = "  ";

        if ($verbose <= self::$maxVerbose) {
            $message = str_repeat($indentStr, self::$indent) . $output;
            $class = '';
            $function = '';
            if (self::$addClass) {
                $debug = debug_backtrace()[1 + $traceLvl] ?? debug_backtrace()[1];
                $class = self::fitString($debug['class'], self::$classLimit);
            }
            if (self::$addFunction) {
                if (!isset($debug)) {
                    $debug = debug_backtrace()[1 + $traceLvl] ?? ['function' => 'not found'];
                }
                if (\mb_strlen($class) > 0) {
                    $function .= ' | ';
                }
                $function .= self::fitString($debug['function'], self::$functionLimit) . ' * ';
            }
            if (\mb_strlen($class) > 0 && \mb_strlen($function) == 0) {
                $class .= ' * ';
            }
            if ($replaceNewLine) {
                echo $class . $function . str_replace("\r", '\r', str_replace("\n", '\n', $message)) . PHP_EOL;
            } else {
                echo $class . $function . str_replace("\n", "\n" . str_repeat($indentStr, self::$indent), $message) . PHP_EOL;
            }
        }
    }

    static protected function fitString(string $string, int $size): string
    {
        $slimed = '';
        if (\mb_strlen($string) > $size) {
            $slimed = \mb_substr($string, 0, (int) (floor($size/2) - 1)) . '..';
            $slimed .= \mb_substr($string, (int) -ceil($size/2) + 1);
        } elseif (\mb_strlen($string) < $size) {
            $amount = $size - \mb_strlen($string);
            $slimed = str_repeat(' ', (int) floor($amount/2)) . $string . str_repeat(' ', (int) ceil($amount/2));
        }
        return $slimed;
    }

    static public function getIndent(): int
    {
        return self::$indent;
    }

    static public function setIndent(int $indent): void
    {
        if ($indent < 0) {
            $indent = 0;
        }
        self::$indent = $indent;
    }

    static public function increaseIndent(): void
    {
        self::$indent++;
    }

    static public function decreaseIndent(): void
    {
        self::$indent--;
        if (self::$indent < 0) {
            self::$indent = 0;
        }
    }

    static public function setVerboseLevel(int $level): void
    {
        self::$verbose = $level;
    }

    static public function setMaxVerboseLevel(int $level): void
    {
        self::$maxVerbose = $level;
    }

    public static function displayBlocks(array $blocks)
    {
        foreach ($blocks as $block) {
            self::log("Block: " . get_class($block));
            self::log("Subtype: " . $block->getSubtype());
            if ($block instanceof Foundation\CommentBlockAbstract) {
                self::log("Instruction: " . $block->getInstruction()->subStr(0, 60));
            } else {
                self::log("Instruction: " . $block->getInstruction());
            }
            self::log("Instruction Start: " . $block->getInstructionStart());
            self::log("Caret: " . $block->getCaret());
            self::log("Name: `" . $block->getName() . "`");
            self::log("Child Index: `" . $block->getChildIndex() . "`");
            if (method_exists($block, 'isPrivate')) {
                self::log("Is Private: `" . ($block->isPrivate() ? 'true' : 'false') . "`");
            }
            if (method_exists($block, 'getValue')) {
                self::log("Value: `" . $block->getValue() . "`");
            }
            if (method_exists($block, 'getOldName')) {
                self::log("Old Name: `" . $block->getOldName() . "`");
            }
            if (method_exists($block, 'getNewName')) {
                self::log("New Name: `" . $block->getNewName() . "`");
            }
            if (method_exists($block, 'getPath')) {
                self::log("Path: `" . $block->getPath() . "`");
            }
            if (method_exists($block, 'getExtendClass')) {
                self::log("Extended: `" . $block->getExtendClass() . "`");
            }
            if (method_exists($block, 'isGenerator')) {
                self::log("Generator: `" . ($block->isGenerator() ? 'true' : 'false') . "`");
            }
            if (method_exists($block, 'getCaseValue')) {
                self::log("Case value: `" . $block->getCaseValue() . "`");
            }
            if (method_exists($block, 'getBreakLabel')) {
                self::log("Break label: `" . $block->getBreakLabel() . "`");
            }
            if (method_exists($block, 'getContinueLabel')) {
                self::log("Continue label: `" . $block->getContinueLabel() . "`");
            }
            if (method_exists($block, 'getArguments')) {
                self::log("Arguments: [" . \sizeof($block->getArguments()) . "] `");
                self::increaseIndent();
                foreach ($block->getArguments() as $argument) {
                    self::displayBlocks($argument);
                }
                self::decreaseIndent();
            }
            if (method_exists($block, 'getCondBlocks')) {
                self::log("Cond block: [" . \sizeof($block->getCondBlocks()) . "] ");
                self::increaseIndent();
                self::displayBlocks($block->getCondBlocks());
                self::decreaseIndent();
            }
            if (method_exists($block, 'getArgBlocks')) {
                self::log("Arg block: [" . \sizeof($block->getArgBlocks()) . "] ");
                self::increaseIndent();
                self::displayBlocks($block->getArgBlocks());
                self::decreaseIndent();
            }
            if (method_exists($block, 'getArgBlock')) {
                self::log("Arg block: [" . \sizeof($block->getArgBlock()) . "] ");
                self::increaseIndent();
                self::displayBlocks($block->getArgBlock());
                self::decreaseIndent();
            }
            if (method_exists($block, 'getBracketBlocks')) {
                self::log("Bracket block: [" . \sizeof($block->getBracketBlocks()) . "] ");
                self::increaseIndent();
                self::displayBlocks($block->getBracketBlocks());
                self::decreaseIndent();
            }
            if (method_exists($block, 'getMethodValues')) {
                $method = $block->getMethodValues();
                if (!\is_null($method)) {
                    self::log("Chain Link Method:");
                    self::increaseIndent();
                    self::displayBlocks([$method]);
                    self::decreaseIndent();
                }
            }
            if (method_exists($block, 'getCatch')) {
                self::log("Catch block: [" . \sizeof($block->getCatch()) . "] ");
                self::increaseIndent();
                self::displayBlocks($block->getCatch());
                self::decreaseIndent();
            }
            if (method_exists($block, 'getFinally')) {
                self::log("Finally block: [" . \sizeof($block->getFinally()) . "] ");
                self::increaseIndent();
                self::displayBlocks($block->getFinally());
                self::decreaseIndent();
            }
            self::log("Alias: `" . $block->getAlias($block->getName()) . "`");
            self::log("Blocks: [" . \sizeof($block->getBlocks()) . "] ");
            self::log("=======");
            self::increaseIndent();
            self::displayBlocks($block->getBlocks());
            self::decreaseIndent();
        }
    }

    public static function boolToStr(bool $bool): string
    {
        return $bool ? 'true' : 'false';
    }
}
