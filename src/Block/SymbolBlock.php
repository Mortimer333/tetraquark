<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate};
use \Tetraquark\Foundation\VariableBlockAbstract as VariableBlock;

class SymbolBlock extends VariableBlock implements Contract\Block
{
    public function objectify(int $start = 0)
    {
        $this->setName('');
        // Find symbol start
        $properStart = $start - (\mb_strlen($this->getSubtype()) - 1);

        $this->setInstruction(self::$content->cutToContent($properStart, $start - $properStart + 1))
            ->setInstructionStart($properStart - 1)
            ->setCaret($start);
    }

    public function recreate(): string
    {
        return $this->replaceVariablesWithAliases($this->getInstruction());
    }
}
