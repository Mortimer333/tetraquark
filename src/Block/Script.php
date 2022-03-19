<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Block as Block;

class Script extends Block implements Contract\Block
{
    public function objectify(int $start = 0)
    {
        $map    = [];
        $item   = [];
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
        Log::log("=======================");
        $this->displayBlocks($this->blocks);
    }

    public function displayBlocks(array $blocks)
    {
        foreach ($blocks as $block) {
            Log::log("Block: " . get_class($block), 1);
            Log::log("Subtype: " . $block->getSubtype(), 1);
            Log::log("Instruction: " . $block->getInstruction());
            Log::log("=======", 1);
            Log::increaseIndent();
            $this->displayBlocks($block->blocks);
            Log::decreaseIndent();
        }
    }
}
