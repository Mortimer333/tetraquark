<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate};
use \Tetraquark\Foundation\ConditionBlockAbstract as ConditionBlock;

class IfBlock extends ConditionBlock implements Contract\Block
{
    protected array $argsBlocks = [];
    protected function getArgs(): string
    {
        $args = '';
        foreach ($this->argsBlocks as $block) {
            $args .= rtrim($block->recreate(), ';');
        }
        return $this->replaceVariablesWithAliases($args);
    }

    public function objectify(int $start = 0)
    {
        $this->setName('');
        $this->setCondType('if');
        $this->setConditionAndInstruction($start);
        $this->argsBlocks = $this->createSubBlocksWithContent($this->getCondition());
        if ($this->getSubtype() === self::SINGLE_CONDITION_SUBTYPE) {
            $this->blocks = $this->createSubBlocksWithContent($this->getSingleCond());
        } else {
            $this->blocks = array_merge($this->blocks, $this->createSubBlocks());
        }
    }
}
