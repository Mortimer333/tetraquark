<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\BlockAbstract as Block;

class ArrayBlock extends Block implements Contract\Block
{
    protected array $endChars = [
        ']' => true,
    ];

    public function objectify(int $start = 0)
    {
        $this->setInstruction(new Content(''));
        $this->setName('');
        $this->setInstructionStart($start);
        $this->blocks = array_merge($this->blocks, $this->createSubBlocks($start + 1));
    }

    public function recreate(): string
    {
        $script = '[';
        foreach ($this->getBlocks() as $block) {
            $script .= trim($block->recreate());
        }
        $script = rtrim($script, ',');
        return $script . ']';
    }
}
