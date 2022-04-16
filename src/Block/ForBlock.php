<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Exception as Exception;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\ConditionBlock as ConditionBlock;

class ForBlock extends ConditionBlock implements Contract\Block
{
    protected string $args = '';
    protected function getArgs(): string
    {
        return $this->removeAdditionalSpaces(
            $this->replaceVariablesWithAliases(
                $this->args
            )
        );
    }

    public function objectify(int $start = 0)
    {
        $this->setName('for');
        $this->setConditionAndInstruction($start);
        $caret = $this->getCaret();

        $condition = explode(';', $this->getCondition());
        if (\sizeof($condition) != 3) {
            throw new Exception('For condition ' . htmlentities($this->getCondition()) , ' is incorrectly formated', 400);
        }

        $caret = $this->getCaret();
        $iteratorCreationBlocks = $this->createSubBlocksForConsition($condition[0]);
        $keepLoopingBlocks      = $this->createSubBlocksForConsition($condition[1]);
        $counterApplyBlocks     = $this->createSubBlocksForConsition($condition[2]);
        Log::log(sizeof($counterApplyBlocks));
        $this->setCondBlocks(array_merge($iteratorCreationBlocks, $keepLoopingBlocks, $counterApplyBlocks));

        $this->args =
            $this->recreateCondBlocks($iteratorCreationBlocks)
            . ';' . $this->recreateCondBlocks($keepLoopingBlocks)
            . ';' . $this->recreateCondBlocks($counterApplyBlocks);

        $this->setCaret($caret);
        $this->blocks = array_merge($this->blocks, $this->createSubBlocks());
    }

    protected function createSubBlocksForConsition(string $condition): array
    {
        $codeSave = self::$content;
        self::$content = $condition;
        $blocks = $this->createSubBlocks(0);
        self::$content = $codeSave;
        return $blocks;
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