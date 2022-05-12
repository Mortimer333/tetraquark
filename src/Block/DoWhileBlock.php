<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log, Exception, Contract, Validate, Content};
use \Tetraquark\Foundation\ConditionBlockAbstract as ConditionBlock;

class DoWhileBlock extends ConditionBlock implements Contract\Block
{
    protected array $endChars = [
        "}" => true
    ];

    protected array  $conditionBlocks = [];

    protected function getArgs(): string
    {
        return $this->replaceVariablesWithAliases(
            rtrim($this->getCondition(), ';')
        );
    }

    public function objectify(int $start = 0)
    {
        $this->setName('');
        $this->setCondType('do-while');
        $this->setInstruction(new Content('do {'));
        $this->setInstructionStart($start - 2);
        for ($i=$start; $i < self::$content->getLength(); $i++) {
            $letter = self::$content->getLetter($i);
            if ($letter == '{') {
                $start = $i + 1;
                break;
            }

            if ($i == self::$content->getLength() - 1) {
                throw new Exception("Start of do..while block not found", 404);
            }
        }
        $this->blocks = array_merge($this->blocks, $this->createSubBlocks($start));
        $sFConditionEnd = false;
        $condStart = null;
        $condEnd = null;
        $parenthesisOpened = 0;
        for ($i=$this->getCaret(); $i < self::$content->getLength(); $i++) {
            $letter = self::$content->getLetter($i);
            if (Validate::isWhitespace($letter)) {
                continue;
            }

            if ($parenthesisOpened > 0 && $letter == ')') {
                $parenthesisOpened--;
                continue;
            }

            if ($sFConditionEnd && $letter == '(') {
                $parenthesisOpened++;
                continue;
            }

            if ($parenthesisOpened > 0) {
                continue;
            }


            if (!$sFConditionEnd && $letter == '(') {
                $sFConditionEnd = true;
                $condStart = $i + 1;
            } elseif ($sFConditionEnd && $letter == ')') {
                $condEnd = $i;
                break;
            }
        }

        if (\is_null($condEnd) || \is_null($condStart)) {
            throw new Exception("Couldn't find the start or end of the condition for do...while at letter : " . $start, 404);
        }

        $this->setCaret($condEnd);
        $this->setCondition(self::$content->iSubStr($condStart, $condEnd));
        $condBlocks = $this->createSubBlocksWithContent($this->getCondition());
        $this->setCondBlocks($condBlocks);
        $this->setCondition(
            $this->recreateCondBlocks($condBlocks)
        );
    }

    public function recreate(): string
    {
        $script = 'do{';

        foreach ($this->getBlocks() as $block) {
            $script .= $block->recreate();
        }

        $script .= '}while(' . $this->getArgs() . ');';

        return $script;
    }
}
