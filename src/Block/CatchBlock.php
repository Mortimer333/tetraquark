<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\ConditionBlockAbstract as ConditionBlock;;

class CatchBlock extends ConditionBlock implements Contract\Block
{
    protected array $argsBlocks = [];
    protected array $finallyAr = [];
    protected function getArgs(): string
    {
        $args = '';
        foreach ($this->argsBlocks as $block) {
            $args .= rtrim($block->recreate(), ';');
        }
        return $args;
    }

    public function objectify(int $start = 0)
    {
        $this->setName('');
        $this->setCondType('catch');
        $this->setConditionAndInstruction($start);
        $this->argsBlocks = $this->createSubBlocksWithContent($this->getCondition());
        $this->blocks = array_merge($this->blocks, $this->createSubBlocks());
        list($word, $pos) = $this->getNextWord($this->getCaret(), self::$content);
        if (\mb_substr($word, 0, 7) === 'finally') {
            // pos: minus the whole word plus 7 to place caret just after `finally` keyword
            $this->setFinally(new FinallyBlock($pos - \mb_strlen($word) + 8, 'finally', $this));
        }
    }

    protected function setFinally(FinallyBlock $finally): self
    {
        $finally->setPlacement('getFinally');
        $finally->setChildIndex(\sizeof($this->finallyAr));
        $this->setCaret($finally->getCaret());
        $this->finallyAr[] = $finally;
        return $this;
    }

    public function getFinally(): array
    {
        return $this->finallyAr;
    }

    public function getArgBlocks(): array
    {
        return $this->argsBlocks;
    }

    public function recreate(): string
    {
        $script = parent::recreate();

        if (\sizeof($this->finallyAr) > 0) {
            foreach ($this->finallyAr as $finally) {
                $script .= $finally->recreate();
            }
        }

        return $script;
    }
}
