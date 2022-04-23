<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\Abstract\MethodBlockAbstract as MethodBlock;

class FunctionBlock extends MethodBlock implements Contract\Block
{
    protected array $endChars = [
        '}' => true
    ];

    protected array $instructionEnds = [
        '{' => true,
    ];

    public function objectify(int $start = 0)
    {
        $this->findMethodEnd($start + \mb_strlen('function'));
        $this->findAndSetName('function ', ['(' => true]);
        $this->blocks = array_merge($this->blocks, $this->createSubBlocks());
        if (\strlen($this->getName()) == 0) {
            $this->setSubtype('anonymous:function');
        }
        $this->findAndSetArguments();
    }

    public function recreate(): string
    {
        $script = 'function ' . $this->getAlias($this->getName()) . '(' . $this->getAliasedArguments() . '){';        
        $blocks = '';

        foreach ($this->getBlocks() as $block) {
            $blocks .= $block->recreate();
        }

        if (\mb_strlen($blocks) > 0) {
            return $script . rtrim($blocks, ';') . ';}';
        }

        return $script . '}';
    }
}
