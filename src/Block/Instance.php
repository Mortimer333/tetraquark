<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Block as Block;

class Instance extends Block implements Contract\Block
{
    protected array $endChars = [
        '}' => true
    ];

    protected array $instructionEnds = [
        '{' => true,
    ];

    public function objectify(int $start = 0)
    {
        $this->findInstructionEnd($start, 'class', $this->instructionEnds);
        $this->createSubBlocks();
        $this->findAndSetName('class ', $this->instructionEnds);
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
