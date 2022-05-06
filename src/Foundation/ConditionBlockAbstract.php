<?php declare(strict_types=1);

namespace Tetraquark\Foundation;
use \Tetraquark\{Exception as Exception, Block as Block, Log as Log, Validate as Validate};

abstract class ConditionBlockAbstract extends BlockAbstract
{
    protected array $endChars = [
        '}' => true
    ];

    protected string $condition = '';
    /** @var array Here we save blocks for later aliasing */
    protected array $condBlocks = [];
    protected string $condType = '';
    protected string $singleCond = '';

    abstract protected function getArgs(): string;

    protected const SINGLE_CONDITION_SUBTYPE = 'single-condition';

    protected function setConditionAndInstruction(int $start)
    {
        $actualStart = $start - \mb_strlen($this->getCondType());
        $condStart = null;
        $condEnd   = null;
        $end       = null;
        $parenthesisOpen = 0;
        for ($i=$start; $i < \mb_strlen(self::$content); $i++) {
            $letter = self::$content[$i];
            if (Validate::isWhitespace($letter)) {
                continue;
            }

            if ($parenthesisOpen > 0 && $letter == ')') {
                $parenthesisOpen--;
                continue;
            }

            if (!\is_null($condStart) && $letter == '(') {
                $parenthesisOpen++;
                continue;
            }

            if ($parenthesisOpen > 0) {
                continue;
            }


            if (\is_null($condStart) && $letter !== '(') {
                throw new Exception("Couldn't find start of parenthesis of condition at letter $i", 404);
            } elseif (\is_null($condStart) && $letter == '(') {
                $condStart = $i + 1;
                continue;
            }

            if (is_null($condEnd) && !\is_null($condStart) && $letter == ')') {
                $condEnd = $i;
                continue;
            }

            if (
                !\is_null($condStart)
                && !\is_null($condEnd)
                && (
                    $letter == '{'
                    || ($this instanceof Block\IfBlock && $letter == ';')
                )
            ) {
                $end = $i + 1;
                if ($letter == ';') {
                    $this->setSubtype(self::SINGLE_CONDITION_SUBTYPE);
                }
                break;
            }

            if (!\is_null($condStart) && !\is_null($condEnd) && !$this instanceof Block\IfBlock) {
                throw new Exception("Unexcepted character when searching for condition block start at letter $i => " . $this::class, 401);
            }
        }

        if (\is_null($condStart) || \is_null($condEnd) || \is_null($end)) {
            throw new Exception("Condition (" . $this->getCondType() . " at letter $start) was not mapped properly, stopping script", 500);
        }
        $this->setCaret($end);
        $this->setInstruction(\mb_substr(self::$content, $actualStart, $end - $actualStart));
        $this->setInstructionStart($actualStart);
        $this->setCondition(\mb_substr(self::$content, $condStart, $condEnd - $condStart));
        if ($this->getSubtype() === self::SINGLE_CONDITION_SUBTYPE) {
            $this->setSingleCond(\mb_substr(self::$content, $condEnd + 1, $end - ($condEnd + 1)));
        }
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
        $script = $this->getCondType() . '(' . $this->getArgs() . ')';
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

    protected function recreateCondBlocks(array $blocks): string
    {
        $str = '';
        foreach ($blocks as $block) {
            $str .= rtrim($block->recreate(), ';');
        }
        return $str;
    }

    protected function setCondType(string $type): void
    {
        $this->condType = $type;
    }

    public function getCondType(): string
    {
        return $this->condType;
    }

    protected function setSingleCond(string $single): void
    {
        $this->singleCond = $single;
    }

    public function getSingleCond(): string
    {
        return $this->singleCond;
    }
}
