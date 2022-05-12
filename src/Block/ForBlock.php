<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate};
use \Tetraquark\Foundation\ConditionBlockAbstract as ConditionBlock;

class ForBlock extends ConditionBlock implements Contract\Block
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
        $this->setCondType('for');
        $this->setConditionAndInstruction($start);

        $condition = explode(';', $this->getCondition());
        if (\sizeof($condition) != 3) {
            throw new Exception('For condition ' . htmlentities($this->getCondition()) , ' is incorrectly formated', 400);
        }

        $iteratorCreationBlocks = $this->createSubBlocksWithContent($condition[0]);
        $keepLoopingBlocks      = $this->createSubBlocksWithContent($condition[1]);
        $counterApplyBlocks     = $this->createSubBlocksWithContent($condition[2]);
        $this->setCondBlocks(array_merge($iteratorCreationBlocks, $keepLoopingBlocks, $counterApplyBlocks));

        $this->setCondition(
            $this->recreateCondBlocks($iteratorCreationBlocks)
            . ';' . $this->recreateCondBlocks($keepLoopingBlocks)
            . ';' . $this->recreateCondBlocks($counterApplyBlocks)
        );

        $this->blocks = array_merge($this->blocks, $this->createSubBlocks());
    }

    protected function recreateCondBlocks(array $blocks): string
    {
        $str = '';
        foreach ($blocks as $block) {
            $str .= rtrim($block->recreate(), ';');
        }
        return $str;
    }
}
