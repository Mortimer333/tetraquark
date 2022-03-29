<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\MethodBlock as MethodBlock;

class Method extends MethodBlock implements Contract\Block
{
    protected array $endChars = [
        '}' => true
    ];

    protected array $instructionEnds = [
        '{' => true,
    ];

    public function objectify(int $start = 0)
    {
        $this->findInstructionEnd($start, 'function', $this->instructionEnds);
        $this->findAndSetName('function ', ['(' => true]);
        $this->createSubBlocks();
        if (\strlen($this->getName()) == 0) {
            $this->setSubtype('anonymous:function');
        }
        $this->findAndSetArguments();
    }

    public function recreate(): string
    {
        $script = 'function ' . $this->getAlias($this->getName()) . '(' . $this->getAliasedArguments() . '){';
        foreach ($this->getBlocks() as $block) {
            $script .= $block->recreate();
        }
        return $script . '}';
    }
}
