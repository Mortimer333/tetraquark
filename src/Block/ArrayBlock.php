<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log as Log, Exception as Exception, Contract as Contract, Validate as Validate};
use \Tetraquark\Foundation\BlockAbstract as Block;

class ArrayBlock extends Block implements Contract\Block
{
    protected array $endChars = [
        ']' => true,
    ];

    public function objectify(int $start = 0)
    {
        $this->setInstruction('');
        $this->setName('');
        $this->setInstructionStart($start);
        $this->blocks = array_merge($this->blocks, $this->createSubBlocks($start + 1));
    }

    public function recreate(): string
    {
        $script = '[';
        foreach ($this->getBlocks() as $block) {
            $script .=
            trim(trim($block->recreate()),';');
        }
        $script = rtrim($script, ',');
        return $script . '];';
    }
}
