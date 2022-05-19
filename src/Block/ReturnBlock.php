<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\BlockAbstract as Block;

class ReturnBlock extends Block implements Contract\Block
{
    public function objectify(int $start = 0)
    {
        $this->setInstruction(new Content('return'));
        $this->setName('');
        $this->setInstructionStart($start - 6);
        $this->setCaret($start);
        $this->blocks = array_merge($this->blocks, $this->createSubBlocks($start + 1));
    }

    public function recreate(): string
    {
        $script = 'return ';
        foreach ($this->getBlocks() as $block) {
            $script .= $block->recreate();
        }
        return rtrim($script, ';') . ';';
    }
}
