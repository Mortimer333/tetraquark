<?php declare(strict_types=1);

namespace Tetraquark\Block;
use \Tetraquark\{Log as Log, Exception as Exception, Contract as Contract, Validate as Validate};
use \Tetraquark\Abstract\ConditionBlockAbstract as ConditionBlock;

class DoWhileBlock extends ConditionBlock implements Contract\Block
{
    protected array $endChars = [
        "}" => true
    ];

    protected array  $conditionBlocks = [];

    protected function getArgs(): string
    {
        return $this->replaceVariablesWithAliases(
            $this->getCondition()
        );
    }

    public function objectify(int $start = 0)
    {
        $this->setName('');
        $this->setCondType('do-while');
        $this->setInstruction('do {');
        $this->setInstructionStart($start - 2);
        for ($i=$start; $i < \mb_strlen(self::$content); $i++) {
            $letter = self::$content[$i];
            if ($letter == '{') {
                $start = $i + 1;
                break;
            }

            if ($i == \mb_strlen(self::$content) - 1) {
                throw new Exception("Start of do..while block not found", 404);
            }
        }
        $this->blocks = array_merge($this->blocks, $this->createSubBlocks($start));
        $sFConditionEnd = false;
        $condStart = null;
        $condEnd = null;
        for ($i=$this->getCaret(); $i < \mb_strlen(self::$content); $i++) {
            $letter = self::$content[$i];
            if (Validate::isWhitespace($letter)) {
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
        $this->setCondition(\mb_substr(self::$content, $condStart, $condEnd - $condStart));

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
