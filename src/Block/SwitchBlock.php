<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate};
use \Tetraquark\Foundation\ConditionBlockAbstract as ConditionBlock;

class SwitchBlock extends ConditionBlock implements Contract\Block
{
    protected string $args = '';
    protected function getArgs(): string
    {
        return $this->replaceVariablesWithAliases(
            $this->getCondition()
        );
    }

    public function objectify(int $start = 0)
    {
        $this->setName('');
        $this->setCondType('switch');
        $this->setConditionAndInstruction($start);

        $condBlocks = $this->createSubBlocksWithContent($this->getCondition());
        $this->setCondBlocks($condBlocks);
        $this->setCondition(
            $this->recreateCondBlocks($condBlocks)
        );
        $this->blocks = array_merge($this->blocks, $this->createSubBlocks());
    }
}
