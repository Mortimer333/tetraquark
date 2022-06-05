<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\MethodBlockAbstract as MethodBlock;

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
        $this->findMethodEnd($start);
        $this->setInstruction(new Content('function' . $this->getInstruction()));
        $this->findAndSetName('function ', ['(' => true]);
        $this->blocks = array_merge($this->blocks, $this->createSubBlocks());
        if (\mb_strlen($this->getName()) == 0) {
            $this->setSubtype('anonymous:function');
        }
        $this->findAndSetArguments();
        $this->setInstructionStart($start - \mb_strlen('function '));
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

    public function recreateForImport(): string
    {
        $script = $this->getAlias($this->getName()) . ' = function(' . $this->getAliasedArguments() . '){';
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
