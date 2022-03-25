<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Block as Block;

class NewInstance extends Block implements Contract\Block
{
    public function objectify(int $start = 0)
    {
        for ($i=0; $i < \mb_strlen(self::$content); $i++) {
            $letter = self::$content[$i];
            if ($this->isWhitespace($letter)) {
                continue;
            }
        }
        $instruction = $this->getInstruction();
        Log::log($instruction);
        if ($instruction) {
            // code...
        }
        $this->createSubBlocks();
    }

    public function recreate(): string
    {
        $script = 'class ' . $this->getAlias($this->getName()) . '{';
        foreach ($this->getBlocks() as $block) {
            $script .= $block->recreate();
        }
        return $script . '}';
    }
}
