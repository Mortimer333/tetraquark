<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log as Log, Exception as Exception, Contract as Contract, Validate as Validate};
use \Tetraquark\Abstract\ConditionBlockAbstract as ConditionBlock;

class SwitchBlock extends ConditionBlock implements Contract\Block
{
    protected string $args = '';
    protected function getArgs(): string
    {
        return $this->removeAdditionalSpaces(
            $this->replaceVariablesWithAliases(
                $this->getCondition()
            )
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
