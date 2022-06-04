<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate};
use \Tetraquark\Foundation\BlockAbstract as Block;

class ClassBlock extends Block implements Contract\Block
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
        $this->blocks = array_merge($this->blocks, $this->createSubBlocks());
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

    public function recreateForImport(): string
    {
        $script = $this->getAlias($this->getName()) . ' = class {';
        foreach ($this->getBlocks() as $block) {
            $script .= $block->recreate();
        }
        return $script . '}';
    }
}
