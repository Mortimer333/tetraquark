<?php declare(strict_types=1);

namespace Tetraquark;

abstract class ConditionBlock extends Block
{
    protected array $endChars = [
        '}' => true
    ];

    protected string $condition = '';
    /** @var array Here we save blocks for later aliasing */
    protected array $condBlocks = [];

    abstract protected function getArgs(): string;

    protected const SINGLE_CONDITION_SUBTYPE = 'single-condition';

    protected function setConditionAndInstruction(int $start)
    {
        $actualStart = $start - \mb_strlen($this->getName()) - 1;
        $condStart = null;
        $condEnd   = null;
        $end       = null;
        for ($i=$start; $i < \mb_strlen(self::$content); $i++) {
            $letter = self::$content[$i];

            if ($this->isWhitespace($letter)) {
                continue;
            }

            if (\is_null($condStart) && $letter !== '(') {
                throw new Exception("Couldn't find start of parenthesis of condition at letter $i", 404);
            } elseif (\is_null($condStart) && $letter == '(') {
                $condStart = $i + 1;
                continue;
            }

            if (!\is_null($condStart) && $letter == ')') {
                $condEnd = $i;
                continue;
            }

            if (
                !\is_null($condStart)
                && !\is_null($condEnd)
                && (
                    $letter == '{'
                    || $this instanceof Block\IfBlock && $letter == ';'
                )
            ) {
                $end = $i + 1;
                if ($letter == ';') {
                    $this->setSubtype(self::SINGLE_CONDITION_SUBTYPE);
                }
                break;
            }

            if (!\is_null($condStart) && !\is_null($condEnd)) {
                throw new Exception("Unexcepted character when searching for condtion block start at letter $i", 401);
            }
        }

        if (\is_null($condStart) || \is_null($condEnd) || \is_null($end)) {
            throw new Exception("Condition (" . $this->getName() . " at letter $start) was not mapped properly, stopping script", 500);
        }

        $this->setCaret($end);
        $this->setInstruction(\mb_substr(self::$content, $actualStart, $end - $actualStart));
        $this->setInstructionStart($actualStart);
        $this->setCondition(\mb_substr(self::$content, $condStart, $condEnd - $condStart));
    }

    protected function setCondition(string $condition): void
    {
        $this->condition = trim($condition);
    }

    protected function getCondition(): string
    {
        return $this->condition;
    }

    protected function setCondBlocks(array $blocks): void
    {
        $this->condBlocks = $blocks;
    }

    public function getCondBlocks(): array
    {
        return $this->condBlocks;
    }

    public function recreate(): string
    {
        $script = $this->getName() . '(' . $this->getArgs() . ')';
        if ($this->getSubtype() !== self::SINGLE_CONDITION_SUBTYPE) {
            $script .= '{';
        }

        foreach ($this->getBlocks() as $block) {
            $script .= $block->recreate();
        }

        if ($this->getSubtype() !== self::SINGLE_CONDITION_SUBTYPE) {
            $script .= '}';
        }

        return $script;
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
