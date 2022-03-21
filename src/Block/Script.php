<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Block as Block;

class Script extends Block implements Contract\Block
{
    /** @var string Minified script */
    protected string $minified = '';

    public function __construct(
        string $content,
        protected int    $start = 0,
        protected string $subtype = '',
        protected array  $data  = []
    ) {
        self::$content = $content;
        parent::__construct($start, $subtype, $data);
    }

    public function objectify(int $start = 0)
    {
        Log::timerStart();
        Log::log("Mapping...");
        $this->map($start);
        Log::log("=======================");
        Log::log("Creating aliases...");
        $this->generateAliases();
        Log::log("=======================");
        Log::log("Recreating...");
        $this->setMinified($this->recreate());
        Log::log("=======================");
        $this->displayBlocks($this->blocks);
        Log::timerEnd();
    }

    protected function map($start): void
    {
        $map    = [];
        $word   = '';
        for ($i=$start; $i < \strlen(self::$content); $i++) {
            $this->setCaret($i);
            $letter = self::$content[$i];
            $word  .= $letter;
            if ($this->isWhitespace($letter)) {
                $word = '';
            }
            Log::log("Letter: " . $letter, 2);

            $block = $this->constructBlock($word, $i);
            if ($block) {
                Log::log("Add Block!", 1);
                $this->blocks[] = $block;
            }
        }
    }

    protected function setMinified(string $minified): self
    {
        $this->minified = $minified;
        return $this;
    }

    public function getMinified(): string
    {
        return $this->minified;
    }

    public function recreate(): string
    {
        $script = '';
        foreach ($this->blocks as $block) {
            $script .= $block->recreate();
        }
        return $script;
    }

    public function displayBlocks(array $blocks)
    {
        foreach ($blocks as $block) {
            Log::log("Block: " . get_class($block));
            Log::log("Subtype: " . $block->getSubtype());
            Log::log("Instruction: " . $block->getInstruction());
            Log::log("Name: `" . $block->getName() . "`");
            if (method_exists($block, 'getValue')) {
                Log::log("Value: `" . $block->getValue() . "`");
            }
            if (method_exists($block, 'getArguments')) {
                Log::log("Arguments: [" . \sizeof($block->getArguments()) . "] `" . implode('`, `', $block->getArguments()) . "`");
            }
            if (method_exists($block, 'getArgumentsAliases')) {
                $aliases = $block->getArgumentsAliases();
                $str = "Argument Aliases: [" . \sizeof($aliases) . "] `";
                foreach ($aliases as $key => $value) {
                    $str .= "$key => $value, ";
                }
                Log::log(rtrim($str, ', ') . "`");
            }
            if (isset($block->alias)) {
                Log::log("Alias: `" . $block->getAlias() . "`");
            }

            $aliases = $block->getAliasesMap();
            $str = "Map of Aliases: [" . \sizeof($aliases) . "] `";
            foreach ($aliases as $key => $value) {
                $str .= "$key=$value, ";
            }
            Log::log(rtrim($str, ', ') . "`");

            Log::log("=======");
            Log::increaseIndent();
            $this->displayBlocks($block->blocks);
            Log::decreaseIndent();
        }
    }
}
