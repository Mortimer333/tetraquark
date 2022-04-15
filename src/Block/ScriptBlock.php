<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Block as Block;

class ScriptBlock extends Block implements Contract\Block
{
    protected array  $endChars = [];
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
        $aliasesStr = "Aliases: ";
        foreach (self::$mappedAliases as $key => $value) {
            $aliasesStr .= "$key => $value, ";
        }
        Log::log($aliasesStr);
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
        $this->setCaret($start);
        $this->blocks = array_merge($this->blocks, $this->createSubBlocks());
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
            Log::log("Instruction Start: " . $block->getInstructionStart());
            Log::log("Name: `" . $block->getName() . "`");
            if (method_exists($block, 'getValue')) {
                Log::log("Value: `" . $block->getValue() . "`");
            }
            if (method_exists($block, 'getArguments')) {
                Log::log("Arguments: [" . \sizeof($block->getArguments()) . "] `" . implode('`, `', $block->getArguments()) . "`");
            }
            Log::log("Alias: `" . $block->getAlias($block->getName()) . "`");
            Log::log("=======");
            Log::increaseIndent();
            $this->displayBlocks($block->blocks);
            Log::decreaseIndent();
        }
    }
}
