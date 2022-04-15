<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\Exception as Exception;
use \Tetraquark\Log as Log;
use \Tetraquark\Contract as Contract;
use \Tetraquark\ConditionBlock as ConditionBlock;

class IfBlock extends ConditionBlock implements Contract\Block
{
    protected array $argsBlocks = [];
    protected function getArgs(): string
    {
        $args = '';
        foreach ($this->argsBlocks as $block) {
            $args .= rtrim($block->recreate(), ';');
        }
        return $this->removeAdditionalSpaces(
            $this->replaceVariablesWithAliases($args)
        );
    }

    public function objectify(int $start = 0)
    {
        $this->setName('if');
        $this->setConditionAndInstruction($start);
        $caret = $this->getCaret();

        $codeSave = self::$content;
        self::$content = $this->getCondition();
        $this->argsBlocks = $this->createSubBlocks(0, true);
        self::$content = $codeSave;
        Log::log('Old caret: ' . $caret . ', new caret ' . $this->getCaret());
        $this->setCaret($caret);
        $this->blocks = array_merge($this->blocks, $this->createSubBlocks());
    }
}
