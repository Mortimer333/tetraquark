<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Exception as Exception;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\ConditionBlock as ConditionBlock;

class WhileBlock extends ConditionBlock implements Contract\Block
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
        $this->setName('while');
        $this->setConditionAndInstruction($start);

        $condBlocks = $this->createSubBlocksWithContent($this->getCondition());
        $this->setCondBlocks($condBlocks);
        $this->setCondition(
            $this->recreateCondBlocks($condBlocks)
        );
        $this->blocks = array_merge($this->blocks, $this->createSubBlocks());
    }
}
